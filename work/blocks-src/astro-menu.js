import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody, TextControl } from '@wordpress/components';

registerBlockType('sgup/astro-menu', {
    title: 'Astronomy Menu',
    icon: 'menu',
    category: 'widgets',

    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Settings">
                        <TextControl
                            label="Menu Slug"
                            value={attributes.which}
                            onChange={(value) => setAttributes({ which: value })}
                            help="Enter the menu identifier (e.g., alert-menu)"
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <div style={{ border: '2px dashed #ccc', padding: '20px', background: '#f5f5f5', borderRadius: '4px' }}>
                        <h3 style={{ margin: '0 0 10px 0' }}>📋 Astronomy Menu</h3>
                        <p style={{ margin: 0, color: '#666' }}>Menu: {attributes.which}</p>
                    </div>
                </div>
            </>
        );
    },

    save: () => null
});