import { addFilter } from '@wordpress/hooks';

// Apply default supports to all sgup blocks
addFilter(
    'blocks.registerBlockType',
    'sgup/default-supports',
    (settings, name) => {
        if (name.startsWith('sgup/')) {
            return {
                ...settings,
                supports: {
                    ...settings.supports,
                    align: true,
                    className: true,
                    customClassName: true,
                    spacing: {
                        margin: true,
                        padding: true,
                        blockGap: true,
                    },
                },
            };
        }
        return settings;
    }
);

// Import all block registrations
import './apod';
import './astro-menu';
import './cme-alerts';
import './flare-alerts';
import './geomag-alerts';
import './latest-alerts';
import './neos';
import './sw-alerts';
import './weather';