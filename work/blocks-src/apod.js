import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody, TextControl } from '@wordpress/components';

registerBlockType('sgup/apod', {
    title: 'Astronomy Picture of the Day',
    icon: 'format-image',
    category: 'sgup_space',

    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Settings">
                        <TextControl
                            label="Title"
                            value={attributes.title}
                            onChange={(value) => setAttributes({ title: value })}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <div style={{ border: '2px dashed #ccc', padding: '20px', background: '#f5f5f5', borderRadius: '4px' }}>
                        <h3 style={{ margin: '0 0 10px 0' }}>🖼️ {attributes.title}</h3>
                        <p style={{ margin: 0, color: '#666' }}>APOD will display here on the front-end.</p>
                    </div>
                </div>
            </>
        );
    },

    save: () => null
});