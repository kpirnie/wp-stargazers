/**
 * SGU Weather Location Handler
 * 
 * Handles browser geolocation, cookie storage, and weather AJAX requests
 * 
 * @package US Star Gazers
 * @since 8.4
 */

(function ($) {
    'use strict';

    // Weather Location Manager
    const SGUWeatherLocation = {

        /**
         * Initialize the location manager
         */
        init: function () {
            this.bindEvents();
            this.checkInitialLocation();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            // Geolocation button click
            $(document).on('click', '.sgu-weather-geolocate', this.requestGeolocation.bind(this));

            // ZIP code form submission
            $(document).on('submit', '.sgu-weather-zip-form', this.handleZipSubmit.bind(this));

            // Change location button
            $(document).on('click', '.sgu-weather-change-location', this.showLocationPicker.bind(this));

            // Refresh weather data
            $(document).on('click', '.sgu-weather-refresh', this.refreshWeather.bind(this));
        },

        /**
         * Check if we have a stored location on page load
         */
        checkInitialLocation: function () {
            const location = this.getStoredLocation();

            if (location) {
                // We have a location, update UI and load weather
                this.updateLocationDisplay(location);
                this.loadWeatherData(location);
            } else if (!sguWeather.hasLocation) {
                // No location stored, request geolocation on first visit
                this.promptForLocation();
            }
        },

        /**
         * Prompt user for location (show location picker)
         */
        promptForLocation: function () {
            // Show the location picker modal/section
            $('.sgu-weather-location-prompt').addClass('active');

            // Auto-request geolocation if supported
            if ('geolocation' in navigator) {
                // Give user a moment to see the UI before requesting
                setTimeout(() => {
                    this.requestGeolocation();
                }, 500);
            }
        },

        /**
         * Request browser geolocation
         */
        requestGeolocation: function (e) {
            if (e) e.preventDefault();

            const $button = $('.sgu-weather-geolocate');
            const originalText = $button.text();

            // Check if geolocation is available
            if (!('geolocation' in navigator)) {
                this.showError('Geolocation is not supported by your browser. Please enter a ZIP code.');
                return;
            }

            // Update button state
            $button.text('Locating...').prop('disabled', true);

            // Request location
            navigator.geolocation.getCurrentPosition(
                // Success callback
                (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;

                    // Save location via AJAX
                    this.saveLocation(lat, lon, 'geolocation');
                    $button.text(originalText).prop('disabled', false);
                },
                // Error callback
                (error) => {
                    $button.text(originalText).prop('disabled', false);

                    let message = 'Unable to get your location. ';

                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            message += 'Location access was denied. Please enter a ZIP code instead.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message += 'Location information unavailable. Please enter a ZIP code.';
                            break;
                        case error.TIMEOUT:
                            message += 'Location request timed out. Please try again or enter a ZIP code.';
                            break;
                        default:
                            message += 'Please enter a ZIP code instead.';
                    }

                    this.showError(message);
                },
                // Options
                {
                    enableHighAccuracy: false,
                    timeout: 10000,
                    maximumAge: 300000 // Cache for 5 minutes
                }
            );
        },

        /**
         * Handle ZIP code form submission
         */
        handleZipSubmit: function (e) {
            e.preventDefault();

            const $form = $(e.target);
            const $input = $form.find('.sgu-weather-zip-input');
            const $button = $form.find('button[type="submit"]');
            const zip = $input.val().trim();

            // Validate ZIP format
            if (!/^\d{5}$/.test(zip)) {
                this.showError('Please enter a valid 5-digit ZIP code.');
                $input.focus();
                return;
            }

            // Update button state
            const originalText = $button.text();
            $button.text('Finding...').prop('disabled', true);

            // Geocode the ZIP via AJAX
            $.ajax({
                url: sguWeather.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'sgu_geocode_zip',
                    nonce: sguWeather.nonce,
                    zip: zip
                },
                success: (response) => {
                    $button.text(originalText).prop('disabled', false);

                    if (response.success) {
                        const location = response.data.location;
                        this.storeLocation(location);
                        this.updateLocationDisplay(location);
                        this.loadWeatherData(location);
                        this.hideLocationPicker();
                    } else {
                        this.showError(response.data.message || 'Could not find that ZIP code.');
                    }
                },
                error: () => {
                    $button.text(originalText).prop('disabled', false);
                    this.showError('Network error. Please try again.');
                }
            });
        },

        /**
         * Save location to server and cookie
         */
        saveLocation: function (lat, lon, source) {
            $.ajax({
                url: sguWeather.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'sgu_save_location',
                    nonce: sguWeather.nonce,
                    lat: lat,
                    lon: lon
                },
                success: (response) => {
                    if (response.success) {
                        const location = response.data.location;
                        this.storeLocation(location);
                        this.updateLocationDisplay(location);
                        this.loadWeatherData(location);
                        this.hideLocationPicker();
                    } else {
                        this.showError(response.data.message || 'Could not save location.');
                    }
                },
                error: () => {
                    this.showError('Network error. Please try again.');
                }
            });
        },

        /**
         * Store location in cookie
         */
        storeLocation: function (location) {
            const cookieValue = JSON.stringify(location);
            const days = sguWeather.cookieDays || 30;
            const expires = new Date();
            expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));

            document.cookie = sguWeather.cookieName + '=' + encodeURIComponent(cookieValue) +
                ';expires=' + expires.toUTCString() +
                ';path=/;SameSite=Lax';
        },

        /**
         * Get stored location from cookie
         */
        getStoredLocation: function () {
            const name = sguWeather.cookieName + '=';
            const cookies = document.cookie.split(';');

            for (let cookie of cookies) {
                cookie = cookie.trim();
                if (cookie.indexOf(name) === 0) {
                    try {
                        return JSON.parse(decodeURIComponent(cookie.substring(name.length)));
                    } catch (e) {
                        return null;
                    }
                }
            }

            return null;
        },

        /**
         * Load weather data via AJAX
         */
        loadWeatherData: function (location) {
            // Find all weather containers and load appropriate data
            $('.sgu-weather-container').each((index, container) => {
                const $container = $(container);
                const type = $container.data('weather-type') || 'current';

                this.fetchWeatherForContainer($container, location, type);
            });
        },

        /**
         * Fetch weather data for a specific container
         */
        fetchWeatherForContainer: function ($container, location, type) {
            const $content = $container.find('.sgu-weather-content');
            const $loading = $container.find('.sgu-weather-loading');

            // Show loading state
            $content.addClass('loading');
            $loading.show();

            $.ajax({
                url: sguWeather.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'sgu_get_weather',
                    nonce: sguWeather.nonce,
                    lat: location.lat,
                    lon: location.lon,
                    type: type
                },
                success: (response) => {
                    $loading.hide();
                    $content.removeClass('loading');

                    if (response.success) {
                        this.renderWeatherData($container, response.data.weather, type);
                    } else {
                        this.showContainerError($container, response.data.message);
                    }
                },
                error: () => {
                    $loading.hide();
                    $content.removeClass('loading');
                    this.showContainerError($container, 'Failed to load weather data.');
                }
            });
        },

        /**
         * Render weather data in container
         */
        renderWeatherData: function ($container, weather, type) {
            // This will be handled by the template system primarily,
            // but we can update dynamic elements here
            $container.trigger('sgu:weatherLoaded', [weather, type]);

            // Update any dynamic elements based on weather data
            if (type === 'current' && weather.main) {
                $container.find('.sgu-weather-temp').text(Math.round(weather.main.temp) + '°F');
                $container.find('.sgu-weather-desc').text(weather.weather[0]?.description || '');
                $container.find('.sgu-weather-humidity').text(weather.main.humidity + '%');
                $container.find('.sgu-weather-wind').text(Math.round(weather.wind?.speed || 0) + ' mph');
            }
        },

        /**
         * Update location display in UI
         */
        updateLocationDisplay: function (location) {
            const displayName = location.name + (location.state ? ', ' + location.state : '');

            $('.sgu-weather-location-name').text(displayName);
            $('.sgu-weather-has-location').show();
            $('.sgu-weather-no-location').hide();
            $('.sgu-weather-location-prompt').removeClass('active');
        },

        /**
         * Show location picker UI
         */
        showLocationPicker: function (e) {
            if (e) e.preventDefault();
            $('.sgu-weather-location-prompt').addClass('active');
        },

        /**
         * Hide location picker UI
         */
        hideLocationPicker: function () {
            $('.sgu-weather-location-prompt').removeClass('active');
        },

        /**
         * Refresh weather data
         */
        refreshWeather: function (e) {
            if (e) e.preventDefault();

            const location = this.getStoredLocation();
            if (location) {
                this.loadWeatherData(location);
            }
        },

        /**
         * Show error message
         */
        showError: function (message) {
            // Remove any existing error messages
            $('.sgu-weather-error').remove();

            // Create and show error
            const $error = $('<div class="sgu-weather-error uk-alert uk-alert-danger">' +
                '<a class="uk-alert-close" uk-close></a>' +
                '<p>' + message + '</p></div>');

            $('.sgu-weather-location-prompt, .sgu-weather-container').first().prepend($error);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                $error.fadeOut(() => $error.remove());
            }, 5000);
        },

        /**
         * Show error in specific container
         */
        showContainerError: function ($container, message) {
            const $error = $container.find('.sgu-weather-error');
            if ($error.length) {
                $error.text(message).show();
            } else {
                $container.find('.sgu-weather-content')
                    .prepend('<div class="sgu-weather-error uk-alert uk-alert-warning">' + message + '</div>');
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        SGUWeatherLocation.init();
    });

    // Export for external use
    window.SGUWeatherLocation = SGUWeatherLocation;

})(jQuery);