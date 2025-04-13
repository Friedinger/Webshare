<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

https://github.com/Friedinger/Webshare

by Friedinger (friedinger.org)

*/

namespace Webshare;


use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Class Output
 *
 * Responsible for handling the output of the web page.
 * It allows to manipulate the HTML content of a web page by replacing nodes and attributes.
 * The content can be loaded from a file or a direct input.
 * The output is then formatted and printed to the browser.
 */
class Output
{
	private DOMDocument $dom;

	/**
	 * Output constructor.
	 *
	 * @param string $content The content of the web page.
	 * @param bool $directInput Flag indicating if the content is a direct input or a file path.
	 */
	public function __construct(string $content, bool $directInput = false)
	{
		$this->dom = new DOMDocument(); // Create new dom document

		if ($directInput) {
			// Direct input content as html string if flag is set
			$output = $content;
		} elseif (Config::ALLOW_PAGE_PHP) {
			// Process php in content file if enabled in config
			ob_start();
			require $content;
			$output = ob_get_clean();
		} else {
			// Get content from file without processing php
			$output = file_get_contents($content);
		}
		$output = mb_encode_numericentity($output, [0x80, 0x10FFFF, 0, ~0], "UTF-8");
		$this->dom->loadHTML($output, LIBXML_NOERROR); // Load html content into dom
	}

	/**
	 * Prints the formatted HTML output.
	 *
	 * This method formats the HTML output in the DOM, removes any existing doctype,
	 * removes empty lines, adds the HTML5 doctype, and then prints the output.
	 *
	 * @return void
	 */
	public function print(): void
	{
		$this->dom->formatOutput = true; // Format html output in dom
		$htmlOutput = $this->dom->saveHTML($this->dom->documentElement); // Get html content from dom
		$htmlOutput = preg_replace("/^<!DOCTYPE[^>]+>/", "", $htmlOutput); // Remove potential existing doctype
		$htmlOutput = preg_replace("/((^[\r\n]+)|([\r\n]+\s*[\r\n]+))/", "\n", $htmlOutput); // Remove empty lines
		echo "<!DOCTYPE html>" . PHP_EOL . $htmlOutput; // Add html 5 doctype and print
	}

	/**
	 * Replaces all occurrences of a given tag with the specified content.
	 * This method replaces both nodes and attributes with the given tag.
	 * Replaces the nodes itself, not only the content of them.
	 * The tag is case-insensitive.
	 *
	 * @param string $tag The tag to be replaced.
	 * @param string $content The content to replace the tag with.
	 * @return void
	 */
	public function replaceAll(string $tag, string $content): void
	{
		$tag = strtolower($tag);
		$this->replaceNode($tag, $content); // Replace nodes with tag
		$this->replaceAttributes($tag, $content); // Replace attributes with tag
	}

	/**
	 * Replaces all occurrences of a given tag with the specified content.
	 * This method replaces both nodes and attributes with the given tag.
	 * Replaces the nodes itself, not only the content of them.
	 * The tag is case-insensitive.
	 * The content is sanitized to prevent XSS attacks.
	 *
	 * @param string $tag The tag to be replaced.
	 * @param string $content The content to replace the tag with.
	 * @return void
	 */
	public function replaceAllSafe(string $tag, string $content): void
	{
		$this->replaceAll($tag, htmlspecialchars($content));
	}

	/**
	 * Replaces the content of nodes with a specified tag with new content.
	 * Preserves the tag and attributes of the nodes.
	 * The tag is case-insensitive.
	 *
	 * @param string $tag The tag of the nodes to be replaced.
	 * @param string $content The new content to replace the nodes with.
	 * @return void
	 */
	public function replaceContent(string $tag, string $content): void
	{
		$tag = strtolower($tag);
		$nodeList = $this->dom->getElementsByTagName($tag); // Get nodes with tag
		if ($nodeList->length == 0) return;

		$replacement = $this->importNodes($content); // Import nodes from content
		foreach ($nodeList as $node) {
			$newNode = $this->dom->createElement($tag); // Create new node with tag
			$this->copyNodeAttributes($node, $newNode); // Copy attributes from old node to new node
			foreach ($replacement as $child) {
				$newNode->appendChild($child->cloneNode(true)); // Append content as html nodes to new node
			}
			$node->parentNode->replaceChild($newNode, $node); // Replace old node with new node
		}
	}

	private function copyNodeAttributes($oldNode, DOMElement $newNode): void
	{
		if ($newNode->nodeType == XML_TEXT_NODE || $newNode->nodeType == XML_DOCUMENT_FRAG_NODE) {
			return; // Skip text nodes
		}
		if ($oldNode->hasAttributes()) {
			foreach ($oldNode->attributes as $attribute) {
				$newNode->setAttribute($attribute->name, $attribute->value); // Copy attributes from old node to new node
			}
		}
	}

	private function replaceNode(string $tag, string $content): void
	{
		$nodeList = $this->dom->getElementsByTagName($tag); // Get nodes with tag
		if ($nodeList->length == 0) return;

		$replacement = $this->importNodes($content); // Import nodes from content
		foreach (iterator_to_array($nodeList) as $node) { // Iterate over nodes with tag
			foreach ($replacement as $child) {
				$child = $child->cloneNode(true);
				$node->parentNode->insertBefore($child, $node); // Insert content as html nodes before old node
			}
			$node->parentNode->removeChild($node); // Remove old node
		}
	}

	private function replaceAttributes(string $tag, string $content): void
	{
		// Find nodes with attributes containing tag
		$xpath = new DOMXPath($this->dom);
		$query = "//" . "*[@*[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '" . strtolower($tag) . "')]]";
		$nodes = $xpath->query($query);

		foreach ($nodes as $node) { // Iterate over nodes with attributes containing tag
			foreach ($node->attributes as $attribute) {
				// Replace tag with content in attribute value
				$attribute->value = str_ireplace(["<{$tag}></{$tag}>", "<{$tag} />", "<{$tag}/>", "<{$tag}>"], $content, $attribute->value);
			}
		}
	}

	private function importNodes(string $value): array
	{
		// If value is not html, return as text node
		if (strip_tags($value) === $value) {
			return [$this->dom->createTextNode($value)];
		}

		$valueDom = new DOMDocument();
		$valueDom->loadHTML(mb_encode_numericentity("<html>" . $value . "</html>", [0x80, 0x10FFFF, 0, ~0], "UTF-8"), LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD); // Load html value into dom

		$importedNodes = [];
		foreach ($valueDom->getElementsByTagName("html")->item(0)->childNodes as $child) {
			array_push($importedNodes, $this->dom->importNode($child, true)); // Import nodes from value dom to main dom and add to array
		}
		return $importedNodes; // Return array of imported nodes
	}

	/**
	 * Retrieves the content of a specific HTML node.
	 * Uses an XPath expression to retrieve the content from the HTML.
	 * If the Xpath expression is not provided, the content of the root node is returned.
	 * If the Xpath expression doesn't start with "//", it is assumed to be a tag name, and the first node with that tag name is selected.
	 *
	 * @param string $xpathExpression The XPath expression to retrieve the content from.
	 * @return string|null The content of the HTML node as a string, or null if the node does not exist.
	 */
	public function getContent(string $xpathExpression = null): string|null
	{

		$nodes = $this->searchXpath($xpathExpression); // Get nodes with xpath expression

		if ($nodes->length == 0) return null;

		// Import content to temporary dom
		$dom = new DOMDocument();
		foreach ($nodes as $node) {
			foreach ($node->childNodes as $child) {
				$dom->appendChild($dom->importNode($child, true));
			}
		}

		// Export content as string
		$content = $dom->saveHTML(); // Save html content from dom
		$content = mb_encode_numericentity($content, [0x80, 0x10FFFF, 0, ~0], "UTF-8");
		$content = str_replace("%20", " ", $content);
		return trim($content); // Return html content as string
	}

	/**
	 * Retrieves the content of a specified HTML node and returns it as an array.
	 * Gets the content of the first node with the specified tag name.
	 * The content is returned as an associative array with the tag names as keys.
	 * If the node contains text content, it is stored under the key "text".
	 *
	 * @param string|null $tag The name of the HTML tag to retrieve the content from. If not provided, the root tag name will be used.
	 * @return array The content of the HTML node as an array.
	 */
	public function getContentArray(string $tag = null): array
	{
		$tag = $tag ?? $this->dom->documentElement->tagName; // Get root tag name if no tag name is given

		$node = $this->dom->getElementsByTagName($tag)->item(0); // Get first node with tag name
		if (!$node) return [];

		return $this->getNodeContentArray($node); // Return content as array by getting content recursively
	}

	private function getNodeContentArray(DOMNode $node): array
	{
		$content = [];
		foreach ($node->childNodes as $child) { // Iterate over child nodes
			if ($child->nodeType == XML_TEXT_NODE && trim($child->nodeValue) != "") {
				$content["text"] = trim($child->nodeValue); // Add text content to array
			} elseif ($child->childNodes->length == 1) {
				$content[$child->nodeName] = $child->nodeValue; // Add single child node content to array
			} elseif ($child->nodeType == XML_TEXT_NODE && $child->childNodes->length == 0) {
				// Do not add empty text nodes
			} else {
				$content[$child->nodeName] = $this->getNodeContentArray($child); // Add child node content to array recursively
			}
		}
		return $content; // Return content array
	}

	/**
	 * Changes the value of an attribute in all nodes that match the given XPath expression.
	 * The attribute is case-insensitive.
	 *
	 * @param string $xpathExpression The XPath expression to select the nodes.
	 * @param string $attribute The name of the attribute to change.
	 * @param string $value The new value of the attribute.
	 */
	public function changeAttribute(string $xpathExpression, string $attribute, string $value): void
	{
		$nodes = $this->searchXpath($xpathExpression);

		foreach ($nodes as $node) {
			if (!$node instanceof DOMElement) continue;
			$node->setAttribute($attribute, $value);
		}
	}

	private function searchXpath(string|null $xpathExpression): \DOMNodeList
	{
		$xpathExpression = $xpathExpression ?? $this->dom->documentElement->tagName; // Get root tag name if no tag name is given
		if (!str_starts_with($xpathExpression, "//")) {
			$xpathExpression = "//{$xpathExpression}[1]"; // Convert tag name to xpath expression
		}

		$xpath = new DOMXPath($this->dom);
		$result = $xpath->query($xpathExpression); // Get nodes with xpath expression
		if ($result === false) {
			throw new \UnexpectedValueException("Invalid XPath expression: {$xpathExpression}");
		}
		return $result;
	}

	/**
	 * Sets the content of a specific HTML node.
	 * If the node does not exist, it is created.
	 * The content is inserted as a child node of the specified parent node.
	 * If no parent node is specified, the content is inserted as a child of the root node.
	 *
	 * @param string $tag The tag of the node to set the content of.
	 * @param string $content The content to set.
	 * @param string|null $parent The tag of the parent node to insert the content into.
	 * @param bool $insertBefore Flag indicating if the content should be inserted before the first child of the parent node (true) or appended as the last child (false).
	 */
	public function setContent(string $tag, string $content, string|null $parent, $insertBefore = false): void
	{
		$element = $this->dom->getElementsByTagName($tag);
		if ($element->length > 0) {
			$element = $element->item(0); // Get element if existing
		} else {
			$element = $this->createChildElement($tag, $parent, $insertBefore); // Create element if not existing
		}

		$nodes = $this->importNodes($content); // Import nodes from content

		foreach ($nodes as $node) {
			$element->appendChild($node); // Import nodes from content and append to element
		}
	}

	private function createChildElement(string $tag, string|null $parent, bool $insertBefore = false): DOMElement
	{
		$parent = $parent ?? $this->dom->documentElement->tagName; // Get root tag name if no parent tag name is given
		$parentList = $this->dom->getElementsByTagName($parent);
		if ($parentList->length == 0) {
			throw new \UnexpectedValueException("Parent tag not found: {$parent}");
		}
		$parentElement = $parentList->item(0); // Get parent element
		$element = $this->dom->createElement($tag); // Create element if not existing
		if ($insertBefore) {
			$parentElement->insertBefore($element, $parentElement->firstChild); // Insert element before first child
		} else {
			$parentElement->appendChild($element); // Append element to parent element
		}
		return $element; // Return element
	}
}
