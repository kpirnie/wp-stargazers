import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody, SelectControl, TextControl, ToggleControl } from '@wordpress/components';

registerBlockType('sgup/moon-phase', {
    title: 'Moon Phase',
    icon: 'visibility',
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
                        <ToggleControl
                            label="Show Title"
                            checked={attributes.showTitle}
                            onChange={(value) => setAttributes({ showTitle: value })}
                        />
                        <ToggleControl
                            label="Show Location Picker"
                            checked={attributes.showLocationPicker}
                            onChange={(value) => setAttributes({ showLocationPicker: value })}
                        />
                    </PanelBody>
                    <PanelBody title="Image Options" initialOpen={false}>
                        <SelectControl
                            label="Moon Style"
                            value={attributes.moonStyle}
                            options={[
                                { label: 'Shaded', value: 'shaded' },
                                { label: 'Sketch', value: 'sketch' },
                                { label: 'Default', value: 'default' },
                            ]}
                            onChange={(value) => setAttributes({ moonStyle: value })}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <div style={{ border: '2px dashed #ccc', padding: '20px', background: '#1a1a2e', borderRadius: '4px' }}>
                        <h3 style={{ margin: '0 0 10px 0', color: '#94a3b8' }}>
                            🌕 {attributes.title}
                        </h3>
                        <p style={{ margin: 0, color: '#aaa' }}>Moon phase image will display here.</p>
                        <p style={{ margin: '5px 0 0 0', fontSize: '11px', color: '#666' }}>
                            <em>Style: {attributes.moonStyle}</em>
                        </p>
                        <p style={{ margin: '5px 0 0 0', fontSize: '11px', color: '#666' }}>
                            <em>Requires AstronomyAPI credentials</em>
                        </p>
                    </div>
                </div>
            </>
        );
    },

    save: () => null
});