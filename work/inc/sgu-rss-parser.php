<?php
/**
 * SGU RSS Parser
 *
 * A generic XML parser that converts XML documents to PHP arrays.
 * Handles namespaces, CDATA sections, and multiple input sources.
 *
 * @package SGU
 * @since 1.0.0
 */

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

if( ! class_exists( 'SGU_RSS_Parser' ) ) {

    
/**
 * SGU RSS Parser
 *
 * A generic XML parser that converts XML documents to PHP arrays.
 * Handles namespaces, CDATA sections, and multiple input sources.
 *
 * @package SGU
 * @since 1.0.0
 */

class SGU_RSS_Parser {

	/**
	 * XML source data
	 *
	 * @var string
	 */
	private $xml_source = '';

	/**
	 * Parse XML from URL, file path, or XML string
	 *
	 * @param string $source URL, file path, or XML string
	 * @param string $type Source type: 'url', 'file', or 'string'
	 * @return array|WP_Error Parsed XML as array or WP_Error on failure
	 */
	public function parse( $source, $type = 'string' ) {
		// Load XML based on source type
		switch ( $type ) {
			case 'url':
				$this->xml_source = $this->load_from_url( $source );
				break;
			case 'file':
				$this->xml_source = $this->load_from_file( $source );
				break;
			case 'string':
			default:
				$this->xml_source = $source;
				break;
		}

		// Check for loading errors
		if ( is_wp_error( $this->xml_source ) ) {
			return $this->xml_source;
		}

		// Clean and validate XML
		$this->xml_source = $this->clean_xml( $this->xml_source );

		// Validate and parse XML
		$parsed = $this->parse_xml( $this->xml_source );

		return $parsed;
	}

	/**
	 * Load XML from URL using WordPress HTTP API
	 *
	 * @param string $url URL to fetch
	 * @return string|WP_Error XML content or WP_Error on failure
	 */
	private function load_from_url( $url ) {
		if ( ! function_exists( 'wp_remote_get' ) ) {
			return new WP_Error( 'wp_function_missing', 'WordPress HTTP API not available' );
		}

		// Validate URL
		if ( ! wp_http_validate_url( $url ) ) {
			return new WP_Error( 'invalid_url', 'Invalid URL provided' );
		}

		// Fetch remote content
		$response = wp_remote_get( $url, array(
			'timeout' => 30,
			'sslverify' => true,
		) );

		// Check for HTTP errors
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			return new WP_Error( 'http_error', sprintf( 'HTTP %d error', $response_code ) );
		}

		return wp_remote_retrieve_body( $response );
	}

	/**
	 * Load XML from local file
	 *
	 * @param string $file_path Path to local file
	 * @return string|WP_Error XML content or WP_Error on failure
	 */
	private function load_from_file( $file_path ) {
		// Check if file exists and is readable
		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			return new WP_Error( 'file_error', 'File does not exist or is not readable' );
		}

		// Use WordPress filesystem API if available
		if ( function_exists( 'WP_Filesystem' ) ) {
			global $wp_filesystem;
			
			if ( ! $wp_filesystem ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				WP_Filesystem();
			}

			if ( $wp_filesystem ) {
				$content = $wp_filesystem->get_contents( $file_path );
				if ( $content === false ) {
					return new WP_Error( 'file_read_error', 'Failed to read file' );
				}
				return $content;
			}
		}

		// Fallback to native PHP
		$content = file_get_contents( $file_path );
		if ( $content === false ) {
			return new WP_Error( 'file_read_error', 'Failed to read file' );
		}

		return $content;
	}

	/**
	 * Clean XML string to ensure proper parsing
	 *
	 * @param string $xml_string Raw XML content
	 * @return string Cleaned XML content
	 */
	private function clean_xml( $xml_string ) {
		// Remove BOM if present
		$xml_string = preg_replace( '/^\xEF\xBB\xBF/', '', $xml_string );
		
		// Trim whitespace
		$xml_string = trim( $xml_string );
		
		// Remove any content before XML declaration or root element
		if ( preg_match( '/<\?xml/i', $xml_string ) ) {
			$xml_string = preg_replace( '/^[^<]*(<\?xml)/i', '$1', $xml_string );
		} else {
			$xml_string = preg_replace( '/^[^<]*(<[^?])/i', '$1', $xml_string );
		}
		
		// Remove any content after closing root tag
		$xml_string = preg_replace( '/(<\/[^>]+>)[^<]*$/s', '$1', $xml_string );
		
		// Fix common entity issues - replace unencoded ampersands
		// This matches & followed by characters that don't form valid entities
		$xml_string = preg_replace( '/&(?!(?:amp|lt|gt|quot|apos|#\d+|#x[0-9a-fA-F]+);)/', '&amp;', $xml_string );
		
		// Encode control characters that aren't allowed in XML (except tab, newline, carriage return)
		$xml_string = preg_replace_callback( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', function( $matches ) {
			return '&#' . ord( $matches[0] ) . ';';
		}, $xml_string );
		
		return $xml_string;
	}

	/**
	 * Parse and validate XML string
	 *
	 * @param string $xml_string XML content to parse
	 * @return array|WP_Error Parsed array or WP_Error on failure
	 */
	private function parse_xml( $xml_string ) {
		// Validate XML is not empty
		if ( empty( trim( $xml_string ) ) ) {
			return new WP_Error( 'empty_xml', 'Empty XML content provided' );
		}

		// Disable XML external entity loading for security
		$previous_value = libxml_disable_entity_loader( true );
		
		// Use internal errors for better error handling
		libxml_use_internal_errors( true );
		libxml_clear_errors();

		// Parse XML
		$xml = simplexml_load_string( $xml_string, 'SimpleXMLElement', LIBXML_NOCDATA );

		// Restore previous entity loader setting
		libxml_disable_entity_loader( $previous_value );

		// Check for parsing errors
		if ( $xml === false ) {
			$errors = libxml_get_errors();
			$error_messages = array();
			
			foreach ( $errors as $error ) {
				$error_messages[] = trim( $error->message );
			}
			
			libxml_clear_errors();
			
			return new WP_Error( 'xml_parse_error', implode( '; ', $error_messages ) );
		}

		libxml_clear_errors();

		// Convert SimpleXMLElement to array
		$array = $this->xml_to_array( $xml );

		return $array;
	}

	/**
	 * Convert SimpleXMLElement to array recursively
	 * Handles namespaces and preserves structure
	 *
	 * @param SimpleXMLElement $xml XML element to convert
	 * @return array Converted array
	 */
	private function xml_to_array( $xml ) {
		// Get all namespaces
		$namespaces = $xml->getNamespaces( true );
		$result = array();

		// Process attributes
		$attributes = $this->get_attributes( $xml, $namespaces );
		if ( ! empty( $attributes ) ) {
			$result['@attributes'] = $attributes;
		}

		// Get text content
		$text = trim( (string) $xml );
		$children = array();

		// Process child elements
		foreach ( $xml->children() as $child_name => $child ) {
			$child_array = $this->xml_to_array( $child );
			
			// Handle multiple children with same name - ALWAYS use array
			if ( isset( $children[ $child_name ] ) ) {
				// Convert to numeric array if not already
				if ( ! isset( $children[ $child_name ][0] ) ) {
					$children[ $child_name ] = array( $children[ $child_name ] );
				}
				$children[ $child_name ][] = $child_array;
			} else {
				// Check if there are multiple siblings with same name
				$siblings = $xml->children();
				$count = 0;
				foreach ( $siblings as $sibling_name => $sibling ) {
					if ( $sibling_name === $child_name ) {
						$count++;
					}
				}
				
				// If multiple siblings, start as array immediately
				if ( $count > 1 ) {
					if ( ! isset( $children[ $child_name ] ) ) {
						$children[ $child_name ] = array();
					}
					$children[ $child_name ][] = $child_array;
				} else {
					$children[ $child_name ] = $child_array;
				}
			}
		}

		// Process namespaced children
		foreach ( $namespaces as $prefix => $namespace ) {
			foreach ( $xml->children( $namespace ) as $child_name => $child ) {
				$prefixed_name = $prefix ? $prefix . ':' . $child_name : $child_name;
				$child_array = $this->xml_to_array( $child );
				
				// Handle multiple children with same name - ALWAYS use array
				if ( isset( $children[ $prefixed_name ] ) ) {
					// Convert to numeric array if not already
					if ( ! isset( $children[ $prefixed_name ][0] ) ) {
						$children[ $prefixed_name ] = array( $children[ $prefixed_name ] );
					}
					$children[ $prefixed_name ][] = $child_array;
				} else {
					// Check if there are multiple siblings with same name
					$siblings = $xml->children( $namespace );
					$count = 0;
					foreach ( $siblings as $sibling_name => $sibling ) {
						if ( $sibling_name === $child_name ) {
							$count++;
						}
					}
					
					// If multiple siblings, start as array immediately
					if ( $count > 1 ) {
						if ( ! isset( $children[ $prefixed_name ] ) ) {
							$children[ $prefixed_name ] = array();
						}
						$children[ $prefixed_name ][] = $child_array;
					} else {
						$children[ $prefixed_name ] = $child_array;
					}
				}
			}
		}

		// Combine results
		if ( ! empty( $children ) ) {
			$result = array_merge( $result, $children );
			// Add text content if exists alongside children
			if ( ! empty( $text ) ) {
				$result['@value'] = $text;
			}
		} elseif ( ! empty( $text ) ) {
			// Only text content, no children
			if ( empty( $attributes ) ) {
				return $text;
			}
			$result['@value'] = $text;
		} elseif ( empty( $result ) ) {
			// Empty element
			return '';
		}

		return $result;
	}

	/**
	 * Extract all attributes including namespaced ones
	 *
	 * @param SimpleXMLElement $xml XML element
	 * @param array $namespaces Available namespaces
	 * @return array Attributes array
	 */
	private function get_attributes( $xml, $namespaces ) {
		$attributes = array();

		// Get regular attributes
		foreach ( $xml->attributes() as $attr_name => $attr_value ) {
			$attributes[ $attr_name ] = (string) $attr_value;
		}

		// Get namespaced attributes
		foreach ( $namespaces as $prefix => $namespace ) {
			foreach ( $xml->attributes( $namespace ) as $attr_name => $attr_value ) {
				$prefixed_name = $prefix ? $prefix . ':' . $attr_name : $attr_name;
				$attributes[ $prefixed_name ] = (string) $attr_value;
			}
		}

		return $attributes;
	}
}
    
}
