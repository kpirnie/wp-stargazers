import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody, RangeControl, SelectControl, TextControl, ToggleControl } from '@wordpress/components';

registerBlockType('sgup/star-chart', {
    title: 'Star Chart',
    icon: 'star-filled',
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
                    <PanelBody title="Chart Options" initialOpen={false}>
                        <SelectControl
                            label="Style"
                            value={attributes.style}
                            options={[
                                { label: 'Default', value: 'default' },
                                { label: 'Inverted', value: 'inverted' },
                                { label: 'Navy', value: 'navy' },
                                { label: 'Red', value: 'red' },
                            ]}
                            onChange={(value) => setAttributes({ style: value })}
                        />
                        <RangeControl
                            label="Zoom"
                            value={attributes.zoom}
                            onChange={(value) => setAttributes({ zoom: value })}
                            min={1}
                            max={6}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <div style={{ border: '2px dashed #ccc', padding: '20px', background: '#1a1a2e', borderRadius: '4px' }}>
                        <h3 style={{ margin: '0 0 10px 0', color: '#60a5fa' }}>
                            ✨ {attributes.title}
                        </h3>
                        <p style={{ margin: 0, color: '#aaa' }}>Star chart will display here.</p>
                        <p style={{ margin: '5px 0 0 0', fontSize: '11px', color: '#666' }}>
                            <em>Style: {attributes.style} | Zoom: {attributes.zoom}</em>
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