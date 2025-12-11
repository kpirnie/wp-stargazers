// Weather Blocks - Gutenberg Editor
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody, RangeControl, TextControl, ToggleControl } from '@wordpress/components';

// Current Weather Block
registerBlockType('sgup/weather-current', {
    title: 'Current Weather',
    icon: 'cloud',
    category: 'widgets',

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
                            label="Show Location Picker"
                            checked={attributes.showLocationPicker}
                            onChange={(value) => setAttributes({ showLocationPicker: value })}
                        />
                        <ToggleControl
                            label="Show Details"
                            checked={attributes.showDetails}
                            onChange={(value) => setAttributes({ showDetails: value })}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <div style={{ border: '2px dashed #ccc', padding: '20px', background: '#e8f4fd', borderRadius: '4px' }}>
                        <h3 style={{ margin: '0 0 10px 0' }}>☀️ {attributes.title}</h3>
                        <p style={{ margin: 0, color: '#666' }}>Current weather conditions will display here.</p>
                        {attributes.showLocationPicker && (
                            <p style={{ margin: '5px 0', fontSize: '12px', color: '#888' }}>
                                <em>Location picker enabled</em>
                            </p>
                        )}
                    </div>
                </div>
            </>
        );
    },

    save: () => null
});

// Daily Forecast Block
registerBlockType('sgup/weather-daily', {
    title: 'Daily Weather Forecast',
    icon: 'calendar',
    category: 'widgets',

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
                            label="Show Location Picker"
                            checked={attributes.showLocationPicker}
                            onChange={(value) => setAttributes({ showLocationPicker: value })}
                        />
                        <ToggleControl
                            label="Show Hourly Breakdown"
                            checked={attributes.showHourly}
                            onChange={(value) => setAttributes({ showHourly: value })}
                        />
                        {attributes.showHourly && (
                            <RangeControl
                                label="Hours to Display"
                                value={attributes.hoursToShow}
                                onChange={(value) => setAttributes({ hoursToShow: value })}
                                min={6}
                                max={48}
                            />
                        )}
                        <ToggleControl
                            label="Include NOAA Forecast"
                            checked={attributes.useNoaa}
                            onChange={(value) => setAttributes({ useNoaa: value })}
                            help="Include detailed text forecast from NOAA"
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <div style={{ border: '2px dashed #ccc', padding: '20px', background: '#fff8e6', borderRadius: '4px' }}>
                        <h3 style={{ margin: '0 0 10px 0' }}>📅 {attributes.title}</h3>
                        <p style={{ margin: 0, color: '#666' }}>Daily forecast will display here.</p>
                        {attributes.showHourly && (
                            <p style={{ margin: '5px 0', fontSize: '12px', color: '#888' }}>
                                <em>Showing {attributes.hoursToShow} hours</em>
                            </p>
                        )}
                        {attributes.useNoaa && (
                            <p style={{ margin: '5px 0', fontSize: '12px', color: '#888' }}>
                                <em>NOAA forecast enabled</em>
                            </p>
                        )}
                    </div>
                </div>
            </>
        );
    },

    save: () => null
});

// Weekly Forecast Block
registerBlockType('sgup/weather-weekly', {
    title: 'Weekly Weather Forecast',
    icon: 'calendar-alt',
    category: 'widgets',

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
                            label="Show Location Picker"
                            checked={attributes.showLocationPicker}
                            onChange={(value) => setAttributes({ showLocationPicker: value })}
                        />
                        <RangeControl
                            label="Days to Display"
                            value={attributes.daysToShow}
                            onChange={(value) => setAttributes({ daysToShow: value })}
                            min={3}
                            max={8}
                        />
                        <ToggleControl
                            label="Include NOAA Forecast"
                            checked={attributes.useNoaa}
                            onChange={(value) => setAttributes({ useNoaa: value })}
                            help="Include detailed text forecast from NOAA"
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <div style={{ border: '2px dashed #ccc', padding: '20px', background: '#e6f7e6', borderRadius: '4px' }}>
                        <h3 style={{ margin: '0 0 10px 0' }}>📆 {attributes.title}</h3>
                        <p style={{ margin: 0, color: '#666' }}>{attributes.daysToShow}-day forecast will display here.</p>
                        {attributes.useNoaa && (
                            <p style={{ margin: '5px 0', fontSize: '12px', color: '#888' }}>
                                <em>NOAA forecast enabled</em>
                            </p>
                        )}
                    </div>
                </div>
            </>
        );
    },

    save: () => null
});

// Weather Alerts Block
registerBlockType('sgup/weather-alerts', {
    title: 'Weather Alerts',
    icon: 'warning',
    category: 'widgets',

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
                            label="Show Location Picker"
                            checked={attributes.showLocationPicker}
                            onChange={(value) => setAttributes({ showLocationPicker: value })}
                        />
                        <RangeControl
                            label="Maximum Alerts"
                            value={attributes.maxAlerts}
                            onChange={(value) => setAttributes({ maxAlerts: value })}
                            min={1}
                            max={10}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <div style={{ border: '2px dashed #ccc', padding: '20px', background: '#ffe6e6', borderRadius: '4px' }}>
                        <h3 style={{ margin: '0 0 10px 0' }}>⚠️ {attributes.title}</h3>
                        <p style={{ margin: 0, color: '#666' }}>NOAA weather alerts will display here when active.</p>
                        <p style={{ margin: '5px 0', fontSize: '12px', color: '#888' }}>
                            <em>Showing up to {attributes.maxAlerts} alerts</em>
                        </p>
                    </div>
                </div>
            </>
        );
    },

    save: () => null
});

// Full Weather Dashboard Block
registerBlockType('sgup/weather-full', {
    title: 'Weather Dashboard',
    icon: 'admin-site-alt3',
    category: 'widgets',

    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Dashboard Settings">
                        <TextControl
                            label="Title"
                            value={attributes.title}
                            onChange={(value) => setAttributes({ title: value })}
                        />
                    </PanelBody>
                    <PanelBody title="Components" initialOpen={true}>
                        <ToggleControl
                            label="Show Current Weather"
                            checked={attributes.showCurrent}
                            onChange={(value) => setAttributes({ showCurrent: value })}
                        />
                        <ToggleControl
                            label="Show Hourly Forecast"
                            checked={attributes.showHourly}
                            onChange={(value) => setAttributes({ showHourly: value })}
                        />
                        <ToggleControl
                            label="Show Daily Forecast"
                            checked={attributes.showDaily}
                            onChange={(value) => setAttributes({ showDaily: value })}
                        />
                        <ToggleControl
                            label="Show Weather Alerts"
                            checked={attributes.showAlerts}
                            onChange={(value) => setAttributes({ showAlerts: value })}
                        />
                        <ToggleControl
                            label="Show NOAA Forecast"
                            checked={attributes.showNoaa}
                            onChange={(value) => setAttributes({ showNoaa: value })}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <div style={{ border: '2px dashed #ccc', padding: '20px', background: '#f0f0f0', borderRadius: '4px' }}>
                        <h3 style={{ margin: '0 0 10px 0' }}>🌤️ {attributes.title}</h3>
                        <p style={{ margin: 0, color: '#666' }}>Full weather dashboard will display here.</p>
                        <div style={{ marginTop: '10px', fontSize: '12px', color: '#888' }}>
                            <strong>Active Components:</strong>
                            <ul style={{ margin: '5px 0', paddingLeft: '20px' }}>
                                {attributes.showCurrent && <li>Current Weather</li>}
                                {attributes.showHourly && <li>Hourly Forecast</li>}
                                {attributes.showDaily && <li>Daily Forecast</li>}
                                {attributes.showAlerts && <li>Weather Alerts</li>}
                                {attributes.showNoaa && <li>NOAA Forecast</li>}
                            </ul>
                        </div>
                    </div>
                </div>
            </>
        );
    },

    save: () => null
});

// Location Picker Block
registerBlockType('sgup/weather-location', {
    title: 'Weather Location Picker',
    icon: 'location',
    category: 'widgets',

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
                            label="Compact Mode"
                            checked={attributes.compact}
                            onChange={(value) => setAttributes({ compact: value })}
                            help="Show a smaller, inline version"
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <div style={{ border: '2px dashed #ccc', padding: '20px', background: '#f5f5f5', borderRadius: '4px' }}>
                        <h3 style={{ margin: '0 0 10px 0' }}>📍 {attributes.title}</h3>
                        <p style={{ margin: 0, color: '#666' }}>Location picker will display here.</p>
                        {attributes.compact && (
                            <p style={{ margin: '5px 0', fontSize: '12px', color: '#888' }}>
                                <em>Compact mode enabled</em>
                            </p>
                        )}
                    </div>
                </div>
            </>
        );
    },

    save: () => null
});