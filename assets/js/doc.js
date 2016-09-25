/*!
 * Lucid Toolbox
 *
 * Readme script
 */
(function( win, undefined ) {
	'use strict';

	if ( ! document.querySelectorAll ) { return; }

	var doc = window.document,
	    headings = doc.querySelectorAll( 'h2, h3' ),
	    linkList = doc.createElement('ul'),
	    content = '',
	    inSubmenu = false,
	    id, text;

	/**
	 * Check if browser has touch events.
	 *
	 * @return {Boolean}
	 */
	function isTouch() {
		// ontouchstart for most browsers, onmsgesturechange for IE10
		return !!( 'ontouchstart' in window ) || !!( 'onmsgesturechange' in window );
	}

	/**
	 * Get first child element node.
	 *
	 * @param {node} el Element to get child from.
	 * @return {node}
	 */
	function getFirstChild( el ) {
		var firstChild = el.firstChild;

		while ( null !== firstChild && 1 !== firstChild.nodeType ) {
			firstChild = firstChild.nextSibling;
		}

		return firstChild;
	}

	content += '<li><a href="../index.html">&#9664;&nbsp;&nbsp;Back</a></li>';
	content += '<li><a href="#intro">Intro</a></li>';

	for ( var i = 0, len = headings.length; i < len; i++ ) {
		text = headings[i].innerText || headings[i].textContent;
		id = text.toLowerCase().replace( /[^a-z\-_]+/, '-' );

		headings[i].id = id;

		if ( ! inSubmenu && 'h3' === headings[i].nodeName.toLowerCase() ) {
			inSubmenu = true;
			content += '\n<ul>';
		} else if ( inSubmenu && 'h3' !== headings[i].nodeName.toLowerCase() ) {
			inSubmenu = false;
			content += '</li>\n</ul></li>';
		} else {
			content += '</li>';
		}

		content += '\n<li><a href="#' + id + '">' + text + '</a>';

		if ( i === len - 1 ) {
			content += '</li>';
		}
	}

	if ( isTouch() ) {
		doc.documentElement.className = doc.documentElement.className.replace( /(\s|^)no-touch(\s|$)/, '$1touch$2' );
	}

	linkList.id = 'nav';
	linkList.innerHTML = content;
	doc.body.insertBefore( linkList, getFirstChild( doc.body ) );
})( window );