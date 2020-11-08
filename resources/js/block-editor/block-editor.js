import domReady from '@wordpress/dom-ready';
import { render } from '@wordpress/element';
import { registerCoreBlocks } from '@wordpress/block-library';
import Editor from './components/editor';

// import './styles.scss';

domReady( function() {
    const settings = window.getdaveSbeSettings || {};

	registerCoreBlocks();
	render( <Editor settings={ settings } />, document.getElementById( 'smartpay-form-block-editor' ) );
} );