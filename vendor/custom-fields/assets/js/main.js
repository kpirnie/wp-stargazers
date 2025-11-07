/**
 *
 * -----------------------------------------------------------
 *
 * Sgu_fw Framework
 * A Simple and Lightweight WordPress Option Framework
 *
 * -----------------------------------------------------------
 *
 */
;(function( $, window, document, undefined ) {
  'use strict';

  //
  // Constants
  //
  var SGU_FW   = SGU_FW || {};

  SGU_FW.funcs = {};

  SGU_FW.vars  = {
    onloaded: false,
    $body: $('body'),
    $window: $(window),
    $document: $(document),
    $form_warning: null,
    is_confirm: false,
    form_modified: false,
    code_themes: [],
    is_rtl: $('body').hasClass('rtl'),
  };

  //
  // Helper Functions
  //
  SGU_FW.helper = {

    //
    // Generate UID
    //
    uid: function( prefix ) {
      return ( prefix || '' ) + Math.random().toString(36).substr(2, 9);
    },

    // Quote regular expression characters
    //
    preg_quote: function( str ) {
      return (str+'').replace(/(\[|\])/g, "\\$1");
    },

    //
    // Reneme input names
    //
    name_nested_replace: function( $selector, field_id ) {

      var checks = [];
      var regex  = new RegExp(SGU_FW.helper.preg_quote(field_id +'[\\d+]'), 'g');

      $selector.find(':radio').each(function() {
        if ( this.checked || this.orginal_checked ) {
          this.orginal_checked = true;
        }
      });

      $selector.each( function( index ) {
        $(this).find(':input').each(function() {
          this.name = this.name.replace(regex, field_id +'['+ index +']');
          if ( this.orginal_checked ) {
            this.checked = true;
          }
        });
      });

    },

    //
    // Debounce
    //
    debounce: function( callback, threshold, immediate ) {
      var timeout;
      return function() {
        var context = this, args = arguments;
        var later = function() {
          timeout = null;
          if ( !immediate ) {
            callback.apply(context, args);
          }
        };
        var callNow = ( immediate && !timeout );
        clearTimeout( timeout );
        timeout = setTimeout( later, threshold );
        if ( callNow ) {
          callback.apply(context, args);
        }
      };
    },

  };

  //
  // Custom clone for textarea and select clone() bug
  //
  $.fn.sgu_fw_clone = function() {

    var base   = $.fn.clone.apply(this, arguments),
        clone  = this.find('select').add(this.filter('select')),
        cloned = base.find('select').add(base.filter('select'));

    for( var i = 0; i < clone.length; ++i ) {
      for( var j = 0; j < clone[i].options.length; ++j ) {

        if ( clone[i].options[j].selected === true ) {
          cloned[i].options[j].selected = true;
        }

      }
    }

    this.find(':radio').each( function() {
      this.orginal_checked = this.checked;
    });

    return base;

  };

  //
  // Expand All Options
  //
  $.fn.sgu_fw_expand_all = function() {
    return this.each( function() {
      $(this).on('click', function( e ) {

        e.preventDefault();
        $('.sgu_fw-wrapper').toggleClass('sgu_fw-show-all');
        $('.sgu_fw-section').sgu_fw_reload_script();
        $(this).find('.fa').toggleClass('fa-indent').toggleClass('fa-outdent');

      });
    });
  };

  //
  // Options Navigation
  //
  $.fn.sgu_fw_nav_options = function() {
    return this.each( function() {

      var $nav    = $(this),
          $window = $(window),
          $wpwrap = $('#wpwrap'),
          $links  = $nav.find('a'),
          $last;

      $window.on('hashchange sgu_fw.hashchange', function() {

        var hash  = window.location.hash.replace('#tab=', '');
        var slug  = hash ? hash : $links.first().attr('href').replace('#tab=', '');
        var $link = $('[data-tab-id="'+slug+'"]');

        if ( $link.length ) {

          $link.closest('.sgu_fw-tab-item').addClass('sgu_fw-tab-expanded').siblings().removeClass('sgu_fw-tab-expanded');

          if( $link.next().is('ul') ) {

            $link = $link.next().find('li').first().find('a');
            slug  = $link.data('tab-id');

          }

          $links.removeClass('sgu_fw-active');
          $link.addClass('sgu_fw-active');

          if ( $last ) {
            $last.addClass('hidden');
          }

          var $section = $('[data-section-id="'+slug+'"]');

          $section.removeClass('hidden');
          $section.sgu_fw_reload_script();

          $('.sgu_fw-section-id').val( $section.index()+1 );

          $last = $section;

          if ( $wpwrap.hasClass('wp-responsive-open') ) {
            $('html, body').animate({scrollTop:($section.offset().top-50)}, 200);
            $wpwrap.removeClass('wp-responsive-open');
          }

        }

      }).trigger('sgu_fw.hashchange');

    });
  };

  //
  // Metabox Tabs
  //
  $.fn.sgu_fw_nav_metabox = function() {
    return this.each( function() {

      var $nav      = $(this),
          $links    = $nav.find('a'),
          $sections = $nav.parent().find('.sgu_fw-section'),
          $last;

      $links.each( function( index ) {

        $(this).on('click', function( e ) {

          e.preventDefault();

          var $link = $(this);

          $links.removeClass('sgu_fw-active');
          $link.addClass('sgu_fw-active');

          if ( $last !== undefined ) {
            $last.addClass('hidden');
          }

          var $section = $sections.eq(index);

          $section.removeClass('hidden');
          $section.sgu_fw_reload_script();

          $last = $section;

        });

      });

      $links.first().trigger('click');

    });
  };

  //
  // Metabox Page Templates Listener
  //
  $.fn.sgu_fw_page_templates = function() {
    if ( this.length ) {

      $(document).on('change', '.editor-page-attributes__template select, #page_template, .edit-post-post-status + div select', function() {

        var maybe_value = $(this).val() || 'default';

        $('.sgu_fw-page-templates').removeClass('sgu_fw-metabox-show').addClass('sgu_fw-metabox-hide');
        $('.sgu_fw-page-'+maybe_value.toLowerCase().replace(/[^a-zA-Z0-9]+/g,'-')).removeClass('sgu_fw-metabox-hide').addClass('sgu_fw-metabox-show');

      });

    }
  };

  //
  // Metabox Post Formats Listener
  //
  $.fn.sgu_fw_post_formats = function() {
    if ( this.length ) {

      $(document).on('change', '.editor-post-format select, #formatdiv input[name="post_format"]', function() {

        var maybe_value = $(this).val() || 'default';

        // Fallback for classic editor version
        maybe_value = ( maybe_value === '0' ) ? 'default' : maybe_value;

        $('.sgu_fw-post-formats').removeClass('sgu_fw-metabox-show').addClass('sgu_fw-metabox-hide');
        $('.sgu_fw-post-format-'+maybe_value).removeClass('sgu_fw-metabox-hide').addClass('sgu_fw-metabox-show');

      });

    }
  };

  //
  // Search
  //
  $.fn.sgu_fw_search = function() {
    return this.each( function() {

      var $this  = $(this),
          $input = $this.find('input');

      $input.on('change keyup', function() {

        var value    = $(this).val(),
            $wrapper = $('.sgu_fw-wrapper'),
            $section = $wrapper.find('.sgu_fw-section'),
            $fields  = $section.find('> .sgu_fw-field:not(.sgu_fw-depend-on)'),
            $titles  = $fields.find('> .sgu_fw-title, .sgu_fw-search-tags');

        if ( value.length > 3 ) {

          $fields.addClass('sgu_fw-metabox-hide');
          $wrapper.addClass('sgu_fw-search-all');

          $titles.each( function() {

            var $title = $(this);

            if ( $title.text().match( new RegExp('.*?' + value + '.*?', 'i') ) ) {

              var $field = $title.closest('.sgu_fw-field');

              $field.removeClass('sgu_fw-metabox-hide');
              $field.parent().sgu_fw_reload_script();

            }

          });

        } else {

          $fields.removeClass('sgu_fw-metabox-hide');
          $wrapper.removeClass('sgu_fw-search-all');

        }

      });

    });
  };

  //
  // Sticky Header
  //
  $.fn.sgu_fw_sticky = function() {
    return this.each( function() {

      var $this     = $(this),
          $window   = $(window),
          $inner    = $this.find('.sgu_fw-header-inner'),
          padding   = parseInt( $inner.css('padding-left') ) + parseInt( $inner.css('padding-right') ),
          offset    = 32,
          scrollTop = 0,
          lastTop   = 0,
          ticking   = false,
          stickyUpdate = function() {

            var offsetTop = $this.offset().top,
                stickyTop = Math.max(offset, offsetTop - scrollTop ),
                winWidth  = $window.innerWidth();

            if ( stickyTop <= offset && winWidth > 782 ) {
              $inner.css({width: $this.outerWidth()-padding});
              $this.css({height: $this.outerHeight()}).addClass( 'sgu_fw-sticky' );
            } else {
              $inner.removeAttr('style');
              $this.removeAttr('style').removeClass( 'sgu_fw-sticky' );
            }

          },
          requestTick = function() {

            if ( !ticking ) {
              requestAnimationFrame( function() {
                stickyUpdate();
                ticking = false;
              });
            }

            ticking = true;

          },
          onSticky  = function() {

            scrollTop = $window.scrollTop();
            requestTick();

          };

      $window.on( 'scroll resize', onSticky);

      onSticky();

    });
  };

  //
  // Dependency System
  //
  $.fn.sgu_fw_dependency = function() {
    return this.each( function() {

      var $this   = $(this),
          $fields = $this.children('[data-controller]');

      if( $fields.length ) {

        var normal_ruleset = $.sgu_fw_deps.createRuleset(),
            global_ruleset = $.sgu_fw_deps.createRuleset(),
            normal_depends = [],
            global_depends = [];

        $fields.each( function() {

          var $field      = $(this),
              controllers = $field.data('controller').split('|'),
              conditions  = $field.data('condition').split('|'),
              values      = $field.data('value').toString().split('|'),
              is_global   = $field.data('depend-global') ? true : false,
              ruleset     = ( is_global ) ? global_ruleset : normal_ruleset;

          $.each(controllers, function( index, depend_id ) {

            var value     = values[index] || '',
                condition = conditions[index] || conditions[0];

            ruleset = ruleset.createRule('[data-depend-id="'+ depend_id +'"]', condition, value);

            ruleset.include($field);

            if ( is_global ) {
              global_depends.push(depend_id);
            } else {
              normal_depends.push(depend_id);
            }

          });

        });

        if ( normal_depends.length ) {
          $.sgu_fw_deps.enable($this, normal_ruleset, normal_depends);
        }

        if ( global_depends.length ) {
          $.sgu_fw_deps.enable(SGU_FW.vars.$body, global_ruleset, global_depends);
        }

      }

    });
  };

  //
  // Field: accordion
  //
  $.fn.sgu_fw_field_accordion = function() {
    return this.each( function() {

      var $titles = $(this).find('.sgu_fw-accordion-title');

      $titles.on('click', function() {

        var $title   = $(this),
            $icon    = $title.find('.sgu_fw-accordion-icon'),
            $content = $title.next();

        if ( $icon.hasClass('fa-angle-right') ) {
          $icon.removeClass('fa-angle-right').addClass('fa-angle-down');
        } else {
          $icon.removeClass('fa-angle-down').addClass('fa-angle-right');
        }

        if ( !$content.data( 'opened' ) ) {

          $content.sgu_fw_reload_script();
          $content.data( 'opened', true );

        }

        $content.toggleClass('sgu_fw-accordion-open');

      });

    });
  };

  //
  // Field: backup
  //
  $.fn.sgu_fw_field_backup = function() {
    return this.each( function() {

      if ( window.wp.customize === undefined ) { return; }

      var base    = this,
          $this   = $(this),
          $body   = $('body'),
          $import = $this.find('.sgu_fw-import'),
          $reset  = $this.find('.sgu_fw-reset');

      base.notificationOverlay = function() {

        if ( wp.customize.notifications && wp.customize.OverlayNotification ) {

          // clear if there is any saved data.
          if ( !wp.customize.state('saved').get() ) {
            wp.customize.state('changesetStatus').set('trash');
            wp.customize.each( function( setting ) { setting._dirty = false; });
            wp.customize.state('saved').set(true);
          }

          // then show a notification overlay
          wp.customize.notifications.add( new wp.customize.OverlayNotification('sgu_fw_field_backup_notification', {
            type: 'default',
            message: '&nbsp;',
            loading: true
          }));

        }

      };

      $reset.on('click', function( e ) {

        e.preventDefault();

        if ( SGU_FW.vars.is_confirm ) {

          base.notificationOverlay();

          window.wp.ajax.post('sgu_fw-reset', {
            unique: $reset.data('unique'),
            nonce: $reset.data('nonce')
          })
          .done( function( response ) {
            window.location.reload(true);
          })
          .fail( function( response ) {
            alert( response.error );
            wp.customize.notifications.remove('sgu_fw_field_backup_notification');
          });

        }

      });

      $import.on('click', function( e ) {

        e.preventDefault();

        if ( SGU_FW.vars.is_confirm ) {

          base.notificationOverlay();

          window.wp.ajax.post( 'sgu_fw-import', {
            unique: $import.data('unique'),
            nonce: $import.data('nonce'),
            data: $this.find('.sgu_fw-import-data').val()
          }).done( function( response ) {
            window.location.reload(true);
          }).fail( function( response ) {
            alert( response.error );
            wp.customize.notifications.remove('sgu_fw_field_backup_notification');
          });

        }

      });

    });
  };

  //
  // Field: background
  //
  $.fn.sgu_fw_field_background = function() {
    return this.each( function() {
      $(this).find('.sgu_fw--background-image').sgu_fw_reload_script();
    });
  };

  //
  // Field: code_editor
  //
  $.fn.sgu_fw_field_code_editor = function() {
    return this.each( function() {

      if ( typeof CodeMirror !== 'function' ) { return; }

      var $this       = $(this),
          $textarea   = $this.find('textarea'),
          $inited     = $this.find('.CodeMirror'),
          data_editor = $textarea.data('editor');

      if ( $inited.length ) {
        $inited.remove();
      }

      var interval = setInterval(function () {
        if ( $this.is(':visible') ) {

          var code_editor = CodeMirror.fromTextArea( $textarea[0], data_editor );

          // load code-mirror theme css.
          if ( data_editor.theme !== 'default' && SGU_FW.vars.code_themes.indexOf(data_editor.theme) === -1 ) {

            var $cssLink = $('<link>');

            $('#sgu_fw-codemirror-css').after( $cssLink );

            $cssLink.attr({
              rel: 'stylesheet',
              id: 'sgu_fw-codemirror-'+ data_editor.theme +'-css',
              href: data_editor.cdnURL +'/theme/'+ data_editor.theme +'.min.css',
              type: 'text/css',
              media: 'all'
            });

            SGU_FW.vars.code_themes.push(data_editor.theme);

          }

          CodeMirror.modeURL = data_editor.cdnURL +'/mode/%N/%N.min.js';
          CodeMirror.autoLoadMode(code_editor, data_editor.mode);

          code_editor.on( 'change', function( editor, event ) {
            $textarea.val( code_editor.getValue() ).trigger('change');
          });

          clearInterval(interval);

        }
      });

    });
  };

  //
  // Field: date
  //
  $.fn.sgu_fw_field_date = function() {
    return this.each( function() {

      var $this    = $(this),
          $inputs  = $this.find('input'),
          settings = $this.find('.sgu_fw-date-settings').data('settings'),
          wrapper  = '<div class="sgu_fw-datepicker-wrapper"></div>';

      var defaults = {
        showAnim: '',
        beforeShow: function(input, inst) {
          $(inst.dpDiv).addClass('sgu_fw-datepicker-wrapper');
        },
        onClose: function( input, inst ) {
          $(inst.dpDiv).removeClass('sgu_fw-datepicker-wrapper');
        },
      };

      settings = $.extend({}, settings, defaults);

      if ( $inputs.length === 2 ) {

        settings = $.extend({}, settings, {
          onSelect: function( selectedDate ) {

            var $this  = $(this),
                $from  = $inputs.first(),
                option = ( $inputs.first().attr('id') === $(this).attr('id') ) ? 'minDate' : 'maxDate',
                date   = $.datepicker.parseDate( settings.dateFormat, selectedDate );

            $inputs.not(this).datepicker('option', option, date );

          }
        });

      }

      $inputs.each( function() {

        var $input = $(this);

        if ( $input.hasClass('hasDatepicker') ) {
          $input.removeAttr('id').removeClass('hasDatepicker');
        }

        $input.datepicker(settings);

      });

    });
  };

  //
  // Field: datetime
  //
  $.fn.sgu_fw_field_datetime = function() {
    return this.each( function() {

      var $this    = $(this),
          $inputs  = $this.find('input'),
          settings = $this.find('.sgu_fw-datetime-settings').data('settings');

      settings = $.extend({}, settings, {
        onReady: function( selectedDates, dateStr, instance) {
          $(instance.calendarContainer).addClass('sgu_fw-flatpickr');
        },
      });

      if ( $inputs.length === 2 ) {
        settings = $.extend({}, settings, {
          onChange: function( selectedDates, dateStr, instance) {
            if ( $(instance.element).data('type') === 'from' ) {
              $inputs.last().get(0)._flatpickr.set( 'minDate', selectedDates[0] );
            } else {
              $inputs.first().get(0)._flatpickr.set( 'maxDate', selectedDates[0] );
            }
          },
        });
      }

      $inputs.each( function() {
        $(this).flatpickr(settings);
      });

    });
  };

  //
  // Field: fieldset
  //
  $.fn.sgu_fw_field_fieldset = function() {
    return this.each( function() {
      $(this).find('.sgu_fw-fieldset-content').sgu_fw_reload_script();
    });
  };

  //
  // Field: gallery
  //
  $.fn.sgu_fw_field_gallery = function() {
    return this.each( function() {

      var $this  = $(this),
          $edit  = $this.find('.sgu_fw-edit-gallery'),
          $clear = $this.find('.sgu_fw-clear-gallery'),
          $list  = $this.find('ul'),
          $input = $this.find('input'),
          $img   = $this.find('img'),
          wp_media_frame;

      $this.on('click', '.sgu_fw-button, .sgu_fw-edit-gallery', function( e ) {

        var $el   = $(this),
            ids   = $input.val(),
            what  = ( $el.hasClass('sgu_fw-edit-gallery') ) ? 'edit' : 'add',
            state = ( what === 'add' && !ids.length ) ? 'gallery' : 'gallery-edit';

        e.preventDefault();

        if ( typeof window.wp === 'undefined' || ! window.wp.media || ! window.wp.media.gallery ) { return; }

         // Open media with state
        if ( state === 'gallery' ) {

          wp_media_frame = window.wp.media({
            library: {
              type: 'image'
            },
            frame: 'post',
            state: 'gallery',
            multiple: true
          });

          wp_media_frame.open();

        } else {

          wp_media_frame = window.wp.media.gallery.edit( '[gallery ids="'+ ids +'"]' );

          if ( what === 'add' ) {
            wp_media_frame.setState('gallery-library');
          }

        }

        // Media Update
        wp_media_frame.on( 'update', function( selection ) {

          $list.empty();

          var selectedIds = selection.models.map( function( attachment ) {

            var item  = attachment.toJSON();
            var thumb = ( item.sizes && item.sizes.thumbnail && item.sizes.thumbnail.url ) ? item.sizes.thumbnail.url : item.url;

            $list.append('<li><img src="'+ thumb +'"></li>');

            return item.id;

          });

          $input.val( selectedIds.join( ',' ) ).trigger('change');
          $clear.removeClass('hidden');
          $edit.removeClass('hidden');

        });

      });

      $clear.on('click', function( e ) {
        e.preventDefault();
        $list.empty();
        $input.val('').trigger('change');
        $clear.addClass('hidden');
        $edit.addClass('hidden');
      });

    });

  };

  //
  // Field: group
  //
  $.fn.sgu_fw_field_group = function() {
    return this.each( function() {

      var $this           = $(this),
          $fieldset    = $this.children('.sgu_fw-fieldset'),
          $group       = $fieldset.length ? $fieldset : $this,
          $wrapper     = $group.children('.sgu_fw-cloneable-wrapper'),
          $hidden      = $group.children('.sgu_fw-cloneable-hidden'),
          $max         = $group.children('.sgu_fw-cloneable-max'),
          $min         = $group.children('.sgu_fw-cloneable-min'),
          title_by     = $wrapper.data('title-by'),
          title_prefix = $wrapper.data('title-by-prefix'),
          field_id     = $wrapper.data('field-id'),
          is_number    = Boolean( Number( $wrapper.data('title-number') ) ),
          max          = parseInt( $wrapper.data('max') ),
          min          = parseInt( $wrapper.data('min') );

      // clear accordion arrows if multi-instance
      if ( $wrapper.hasClass('ui-accordion') ) {
        $wrapper.find('.ui-accordion-header-icon').remove();
      }

      var update_title_numbers = function( $selector ) {
        $selector.find('.sgu_fw-cloneable-title-number').each( function( index ) {
          $(this).html( ( $(this).closest('.sgu_fw-cloneable-item').index()+1 ) + '.' );
        });
      };

      $wrapper.accordion({
        header: '> .sgu_fw-cloneable-item > .sgu_fw-cloneable-title',
        collapsible : true,
        active: false,
        animate: false,
        heightStyle: 'content',
        icons: {
          'header': 'sgu_fw-cloneable-header-icon fas fa-angle-right',
          'activeHeader': 'sgu_fw-cloneable-header-icon fas fa-angle-down'
        },
        activate: function( event, ui ) {

          var $panel  = ui.newPanel;
          var $header = ui.newHeader;

          if ( $panel.length && !$panel.data( 'opened' ) ) {

            var $title = $header.find('.sgu_fw-cloneable-value');
            var inputs = [];

            $.each(title_by, function( key, title_key ) {
              inputs.push($panel.find( '[data-depend-id="'+ title_key +'"]' ));
            });

            $.each(inputs, function( key, $input ) {

              $input.on('change keyup sgu_fw.keyup', function() {

                var titles = [];

                $.each(inputs, function( key, $input ) {

                  var input_value = $input.val();

                  if ( input_value ) {
                    titles.push( input_value );
                  }

                });

                if ( titles.length ) {
                  $title.text( titles.join( title_prefix ) );
                }

              }).trigger('sgu_fw.keyup');

            });

            $panel.sgu_fw_reload_script();
            $panel.data( 'opened', true );
            $panel.data( 'retry', false );

          } else if ( $panel.data( 'retry' ) ) {

            $panel.sgu_fw_reload_script_retry();
            $panel.data( 'retry', false );

          }

        }
      });

      $wrapper.sortable({
        axis: 'y',
        handle: '.sgu_fw-cloneable-title,.sgu_fw-cloneable-sort',
        helper: 'original',
        cursor: 'move',
        placeholder: 'widget-placeholder',
        start: function( event, ui ) {

          $wrapper.accordion({ active:false });
          $wrapper.sortable('refreshPositions');
          ui.item.children('.sgu_fw-cloneable-content').data('retry', true);

        },
        update: function( event, ui ) {

          SGU_FW.helper.name_nested_replace( $wrapper.children('.sgu_fw-cloneable-item'), field_id );
          $wrapper.sgu_fw_customizer_refresh();

          if ( is_number ) {
            update_title_numbers($wrapper);
          }

        },
      });

      $group.children('.sgu_fw-cloneable-add').on('click', function( e ) {

        e.preventDefault();

        var count = $wrapper.children('.sgu_fw-cloneable-item').length;

        $min.hide();

        if ( max && (count+1) > max ) {
          $max.show();
          return;
        }

        var $cloned_item = $hidden.sgu_fw_clone(true);

        $cloned_item.removeClass('sgu_fw-cloneable-hidden');

        $cloned_item.find(':input[name!="_pseudo"]').each( function() {
          this.name = this.name.replace( '___', '' ).replace( field_id +'[0]', field_id +'['+ count +']' );
        });

        $wrapper.append($cloned_item);
        $wrapper.accordion('refresh');
        $wrapper.accordion({active: count});
        $wrapper.sgu_fw_customizer_refresh();
        $wrapper.sgu_fw_customizer_listen({closest: true});

        if ( is_number ) {
          update_title_numbers($wrapper);
        }

      });

      var event_clone = function( e ) {

        e.preventDefault();

        var count = $wrapper.children('.sgu_fw-cloneable-item').length;

        $min.hide();

        if ( max && (count+1) > max ) {
          $max.show();
          return;
        }

        var $this           = $(this),
            $parent         = $this.parent().parent(),
            $cloned_helper  = $parent.children('.sgu_fw-cloneable-helper').sgu_fw_clone(true),
            $cloned_title   = $parent.children('.sgu_fw-cloneable-title').sgu_fw_clone(),
            $cloned_content = $parent.children('.sgu_fw-cloneable-content').sgu_fw_clone(),
            $cloned_item    = $('<div class="sgu_fw-cloneable-item" />');

        $cloned_item.append($cloned_helper);
        $cloned_item.append($cloned_title);
        $cloned_item.append($cloned_content);

        $wrapper.children().eq($parent.index()).after($cloned_item);

        SGU_FW.helper.name_nested_replace( $wrapper.children('.sgu_fw-cloneable-item'), field_id );

        $wrapper.accordion('refresh');
        $wrapper.sgu_fw_customizer_refresh();
        $wrapper.sgu_fw_customizer_listen({closest: true});

        if ( is_number ) {
          update_title_numbers($wrapper);
        }

      };

      $wrapper.children('.sgu_fw-cloneable-item').children('.sgu_fw-cloneable-helper').on('click', '.sgu_fw-cloneable-clone', event_clone);
      $group.children('.sgu_fw-cloneable-hidden').children('.sgu_fw-cloneable-helper').on('click', '.sgu_fw-cloneable-clone', event_clone);

      var event_remove = function( e ) {

        e.preventDefault();

        var count = $wrapper.children('.sgu_fw-cloneable-item').length;

        $max.hide();
        $min.hide();

        if ( min && (count-1) < min ) {
          $min.show();
          return;
        }

        $(this).closest('.sgu_fw-cloneable-item').remove();

        SGU_FW.helper.name_nested_replace( $wrapper.children('.sgu_fw-cloneable-item'), field_id );

        $wrapper.sgu_fw_customizer_refresh();

        if ( is_number ) {
          update_title_numbers($wrapper);
        }

      };

      $wrapper.children('.sgu_fw-cloneable-item').children('.sgu_fw-cloneable-helper').on('click', '.sgu_fw-cloneable-remove', event_remove);
      $group.children('.sgu_fw-cloneable-hidden').children('.sgu_fw-cloneable-helper').on('click', '.sgu_fw-cloneable-remove', event_remove);

    });
  };

  //
  // Field: icon
  //
  $.fn.sgu_fw_field_icon = function() {
    return this.each( function() {

      var $this = $(this);

      $this.on('click', '.sgu_fw-icon-add', function( e ) {

        e.preventDefault();

        var $button = $(this);
        var $modal  = $('#sgu_fw-modal-icon');

        $modal.removeClass('hidden');

        SGU_FW.vars.$icon_target = $this;

        if ( !SGU_FW.vars.icon_modal_loaded ) {

          $modal.find('.sgu_fw-modal-loading').show();

          window.wp.ajax.post( 'sgu_fw-get-icons', {
            nonce: $button.data('nonce')
          }).done( function( response ) {

            $modal.find('.sgu_fw-modal-loading').hide();

            SGU_FW.vars.icon_modal_loaded = true;

            var $load = $modal.find('.sgu_fw-modal-load').html( response.content );

            $load.on('click', 'i', function( e ) {

              e.preventDefault();

              var icon = $(this).attr('title');

              SGU_FW.vars.$icon_target.find('.sgu_fw-icon-preview i').removeAttr('class').addClass(icon);
              SGU_FW.vars.$icon_target.find('.sgu_fw-icon-preview').removeClass('hidden');
              SGU_FW.vars.$icon_target.find('.sgu_fw-icon-remove').removeClass('hidden');
              SGU_FW.vars.$icon_target.find('input').val(icon).trigger('change');

              $modal.addClass('hidden');

            });

            $modal.on('change keyup', '.sgu_fw-icon-search', function() {

              var value  = $(this).val(),
                  $icons = $load.find('i');

              $icons.each( function() {

                var $elem = $(this);

                if ( $elem.attr('title').search( new RegExp( value, 'i' ) ) < 0 ) {
                  $elem.hide();
                } else {
                  $elem.show();
                }

              });

            });

            $modal.on('click', '.sgu_fw-modal-close, .sgu_fw-modal-overlay', function() {
              $modal.addClass('hidden');
            });

          }).fail( function( response ) {
            $modal.find('.sgu_fw-modal-loading').hide();
            $modal.find('.sgu_fw-modal-load').html( response.error );
            $modal.on('click', function() {
              $modal.addClass('hidden');
            });
          });
        }

      });

      $this.on('click', '.sgu_fw-icon-remove', function( e ) {
        e.preventDefault();
        $this.find('.sgu_fw-icon-preview').addClass('hidden');
        $this.find('input').val('').trigger('change');
        $(this).addClass('hidden');
      });

    });
  };

  //
  // Field: map
  //
  $.fn.sgu_fw_field_map = function() {
    return this.each( function() {

      if ( typeof L === 'undefined' ) { return; }

      var $this         = $(this),
          $map          = $this.find('.sgu_fw--map-osm'),
          $search_input = $this.find('.sgu_fw--map-search input'),
          $latitude     = $this.find('.sgu_fw--latitude'),
          $longitude    = $this.find('.sgu_fw--longitude'),
          $zoom         = $this.find('.sgu_fw--zoom'),
          map_data      = $map.data( 'map' );

      var mapInit = L.map( $map.get(0), map_data);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(mapInit);

      var mapMarker = L.marker(map_data.center,{draggable: true}).addTo(mapInit);

      var update_latlng = function( data ) {
        $latitude.val( data.lat );
        $longitude.val( data.lng );
        $zoom.val( mapInit.getZoom() );
      };

      mapInit.on( 'click', function ( data ) {
        mapMarker.setLatLng( data.latlng );
        update_latlng( data.latlng );
      });

      mapInit.on( 'zoom', function () {
        update_latlng( mapMarker.getLatLng() );
      });

      mapMarker.on( 'drag', function () {
        update_latlng( mapMarker.getLatLng() );
      });

      if ( ! $search_input.length ) {
        $search_input = $( '[data-depend-id="'+ $this.find('.sgu_fw--address-field').data( 'address-field' ) +'"]' );
      }

      var cache = {};

      $search_input.autocomplete({
        source: function ( request, response ) {

          var term = request.term;

          if ( term in cache ) {
            response( cache[term] );
            return;
          }

          $.get( 'https://nominatim.openstreetmap.org/search', {
            format: 'json',
            q: term,
          }, function( results ) {

            var data;

            if ( results.length ) {
              data = results.map( function( item ) {
                return {
                  value: item.display_name,
                  label: item.display_name,
                  lat: item.lat,
                  lon: item.lon
                };
              }, 'json');
            } else {
              data = [{
                value: 'no-data',
                label: 'No Results.'
              }];
            }

            cache[term] = data;
            response(data);

          });

        },
        select: function ( event, ui ) {

          if ( ui.item.value === 'no-data' ) { return false; }

          var latLng = L.latLng( ui.item.lat, ui.item.lon );

          mapInit.panTo( latLng );
          mapMarker.setLatLng( latLng );
          update_latlng( latLng );

        },
        create: function (event, ui) {
          $(this).autocomplete('widget').addClass('sgu_fw-map-ui-autocomplate');
        }
      });

      var input_update_latlng = function() {

        var latLng = L.latLng( $latitude.val(), $longitude.val() );

        mapInit.panTo( latLng );
        mapMarker.setLatLng( latLng );

      };

      $latitude.on('change', input_update_latlng );
      $longitude.on('change', input_update_latlng );

    });
  };

  //
  // Field: link
  //
  $.fn.sgu_fw_field_link = function() {
    return this.each( function() {

      var $this   = $(this),
          $link   = $this.find('.sgu_fw--link'),
          $add    = $this.find('.sgu_fw--add'),
          $edit   = $this.find('.sgu_fw--edit'),
          $remove = $this.find('.sgu_fw--remove'),
          $result = $this.find('.sgu_fw--result'),
          uniqid  = SGU_FW.helper.uid('sgu_fw-wplink-textarea-');

      $add.on('click', function( e ) {

        e.preventDefault();

        window.wpLink.open(uniqid);

      });

      $edit.on('click', function( e ) {

        e.preventDefault();

        $add.trigger('click');

        $('#wp-link-url').val($this.find('.sgu_fw--url').val());
        $('#wp-link-text').val($this.find('.sgu_fw--text').val());
        $('#wp-link-target').prop('checked', ($this.find('.sgu_fw--target').val() === '_blank'));

      });

      $remove.on('click', function( e ) {

        e.preventDefault();

        $this.find('.sgu_fw--url').val('').trigger('change');
        $this.find('.sgu_fw--text').val('');
        $this.find('.sgu_fw--target').val('');

        $add.removeClass('hidden');
        $edit.addClass('hidden');
        $remove.addClass('hidden');
        $result.parent().addClass('hidden');

      });

      $link.attr('id', uniqid).on('change', function() {

        var atts   = window.wpLink.getAttrs(),
            href   = atts.href,
            text   = $('#wp-link-text').val(),
            target = ( atts.target ) ? atts.target : '';

        $this.find('.sgu_fw--url').val(href).trigger('change');
        $this.find('.sgu_fw--text').val(text);
        $this.find('.sgu_fw--target').val(target);

        $result.html('{url:"'+href+'", text:"'+text+'", target:"'+target+'"}');

        $add.addClass('hidden');
        $edit.removeClass('hidden');
        $remove.removeClass('hidden');
        $result.parent().removeClass('hidden');

      });

    });

  };

  //
  // Field: media
  //
  $.fn.sgu_fw_field_media = function() {
    return this.each( function() {

      var $this            = $(this),
          $upload_button   = $this.find('.sgu_fw--button'),
          $remove_button   = $this.find('.sgu_fw--remove'),
          $library         = $upload_button.data('library') && $upload_button.data('library').split(',') || '',
          $auto_attributes = ( $this.hasClass('sgu_fw-assign-field-background') ) ? $this.closest('.sgu_fw-field-background').find('.sgu_fw--auto-attributes') : false,
          wp_media_frame;

      $upload_button.on('click', function( e ) {

        e.preventDefault();

        if ( typeof window.wp === 'undefined' || ! window.wp.media || ! window.wp.media.gallery ) {
          return;
        }

        if ( wp_media_frame ) {
          wp_media_frame.open();
          return;
        }

        wp_media_frame = window.wp.media({
          library: {
            type: $library
          }
        });

        wp_media_frame.on( 'select', function() {

          var thumbnail;
          var attributes   = wp_media_frame.state().get('selection').first().attributes;
          var preview_size = $upload_button.data('preview-size') || 'thumbnail';

          if ( $library.length && $library.indexOf(attributes.subtype) === -1 && $library.indexOf(attributes.type) === -1 ) {
            return;
          }

          $this.find('.sgu_fw--id').val( attributes.id );
          $this.find('.sgu_fw--width').val( attributes.width );
          $this.find('.sgu_fw--height').val( attributes.height );
          $this.find('.sgu_fw--alt').val( attributes.alt );
          $this.find('.sgu_fw--title').val( attributes.title );
          $this.find('.sgu_fw--description').val( attributes.description );

          if ( typeof attributes.sizes !== 'undefined' && typeof attributes.sizes.thumbnail !== 'undefined' && preview_size === 'thumbnail' ) {
            thumbnail = attributes.sizes.thumbnail.url;
          } else if ( typeof attributes.sizes !== 'undefined' && typeof attributes.sizes.full !== 'undefined' ) {
            thumbnail = attributes.sizes.full.url;
          } else if ( attributes.type === 'image' ) {
            thumbnail = attributes.url;
          } else {
            thumbnail = attributes.icon;
          }

          if ( $auto_attributes ) {
            $auto_attributes.removeClass('sgu_fw--attributes-hidden');
          }

          $remove_button.removeClass('hidden');

          $this.find('.sgu_fw--preview').removeClass('hidden');
          $this.find('.sgu_fw--src').attr('src', thumbnail);
          $this.find('.sgu_fw--thumbnail').val( thumbnail );
          $this.find('.sgu_fw--url').val( attributes.url ).trigger('change');

        });

        wp_media_frame.open();

      });

      $remove_button.on('click', function( e ) {

        e.preventDefault();

        if ( $auto_attributes ) {
          $auto_attributes.addClass('sgu_fw--attributes-hidden');
        }

        $remove_button.addClass('hidden');
        $this.find('input').val('');
        $this.find('.sgu_fw--preview').addClass('hidden');
        $this.find('.sgu_fw--url').trigger('change');

      });

    });

  };

  //
  // Field: repeater
  //
  $.fn.sgu_fw_field_repeater = function() {
    return this.each( function() {

      var $this     = $(this),
          $fieldset = $this.children('.sgu_fw-fieldset'),
          $repeater = $fieldset.length ? $fieldset : $this,
          $wrapper  = $repeater.children('.sgu_fw-repeater-wrapper'),
          $hidden   = $repeater.children('.sgu_fw-repeater-hidden'),
          $max      = $repeater.children('.sgu_fw-repeater-max'),
          $min      = $repeater.children('.sgu_fw-repeater-min'),
          field_id  = $wrapper.data('field-id'),
          max       = parseInt( $wrapper.data('max') ),
          min       = parseInt( $wrapper.data('min') );

      $wrapper.children('.sgu_fw-repeater-item').children('.sgu_fw-repeater-content').sgu_fw_reload_script();

      $wrapper.sortable({
        axis: 'y',
        handle: '.sgu_fw-repeater-sort',
        helper: 'original',
        cursor: 'move',
        placeholder: 'widget-placeholder',
        update: function( event, ui ) {

          SGU_FW.helper.name_nested_replace( $wrapper.children('.sgu_fw-repeater-item'), field_id );
          $wrapper.sgu_fw_customizer_refresh();
          ui.item.sgu_fw_reload_script_retry();

        }
      });

      $repeater.children('.sgu_fw-repeater-add').on('click', function( e ) {

        e.preventDefault();

        var count = $wrapper.children('.sgu_fw-repeater-item').length;

        $min.hide();

        if ( max && (count+1) > max ) {
          $max.show();
          return;
        }

        var $cloned_item = $hidden.sgu_fw_clone(true);

        $cloned_item.removeClass('sgu_fw-repeater-hidden');

        $cloned_item.find(':input[name!="_pseudo"]').each( function() {
          this.name = this.name.replace( '___', '' ).replace( field_id +'[0]', field_id +'['+ count +']' );
        });

        $wrapper.append($cloned_item);
        $cloned_item.children('.sgu_fw-repeater-content').sgu_fw_reload_script();
        $wrapper.sgu_fw_customizer_refresh();
        $wrapper.sgu_fw_customizer_listen({closest: true});

      });

      var event_clone = function( e ) {

        e.preventDefault();

        var count = $wrapper.children('.sgu_fw-repeater-item').length;

        $min.hide();

        if ( max && (count+1) > max ) {
          $max.show();
          return;
        }

        var $this           = $(this),
            $parent         = $this.parent().parent().parent(),
            $cloned_content = $parent.children('.sgu_fw-repeater-content').sgu_fw_clone(),
            $cloned_helper  = $parent.children('.sgu_fw-repeater-helper').sgu_fw_clone(true),
            $cloned_item    = $('<div class="sgu_fw-repeater-item" />');

        $cloned_item.append($cloned_content);
        $cloned_item.append($cloned_helper);

        $wrapper.children().eq($parent.index()).after($cloned_item);

        $cloned_item.children('.sgu_fw-repeater-content').sgu_fw_reload_script();

        SGU_FW.helper.name_nested_replace( $wrapper.children('.sgu_fw-repeater-item'), field_id );

        $wrapper.sgu_fw_customizer_refresh();
        $wrapper.sgu_fw_customizer_listen({closest: true});

      };

      $wrapper.children('.sgu_fw-repeater-item').children('.sgu_fw-repeater-helper').on('click', '.sgu_fw-repeater-clone', event_clone);
      $repeater.children('.sgu_fw-repeater-hidden').children('.sgu_fw-repeater-helper').on('click', '.sgu_fw-repeater-clone', event_clone);

      var event_remove = function( e ) {

        e.preventDefault();

        var count = $wrapper.children('.sgu_fw-repeater-item').length;

        $max.hide();
        $min.hide();

        if ( min && (count-1) < min ) {
          $min.show();
          return;
        }

        $(this).closest('.sgu_fw-repeater-item').remove();

        SGU_FW.helper.name_nested_replace( $wrapper.children('.sgu_fw-repeater-item'), field_id );

        $wrapper.sgu_fw_customizer_refresh();

      };

      $wrapper.children('.sgu_fw-repeater-item').children('.sgu_fw-repeater-helper').on('click', '.sgu_fw-repeater-remove', event_remove);
      $repeater.children('.sgu_fw-repeater-hidden').children('.sgu_fw-repeater-helper').on('click', '.sgu_fw-repeater-remove', event_remove);

    });
  };

  //
  // Field: slider
  //
  $.fn.sgu_fw_field_slider = function() {
    return this.each( function() {

      var $this   = $(this),
          $input  = $this.find('input'),
          $slider = $this.find('.sgu_fw-slider-ui'),
          data    = $input.data(),
          value   = $input.val() || 0;

      if ( $slider.hasClass('ui-slider') ) {
        $slider.empty();
      }

      $slider.slider({
        range: 'min',
        value: value,
        min: data.min || 0,
        max: data.max || 100,
        step: data.step || 1,
        slide: function( e, o ) {
          $input.val( o.value ).trigger('change');
        }
      });

      $input.on('keyup', function() {
        $slider.slider('value', $input.val());
      });

    });
  };

  //
  // Field: sortable
  //
  $.fn.sgu_fw_field_sortable = function() {
    return this.each( function() {

      var $sortable = $(this).find('.sgu_fw-sortable');

      $sortable.sortable({
        axis: 'y',
        helper: 'original',
        cursor: 'move',
        placeholder: 'widget-placeholder',
        update: function( event, ui ) {
          $sortable.sgu_fw_customizer_refresh();
        }
      });

      $sortable.find('.sgu_fw-sortable-content').sgu_fw_reload_script();

    });
  };

  //
  // Field: sorter
  //
  $.fn.sgu_fw_field_sorter = function() {
    return this.each( function() {

      var $this         = $(this),
          $enabled      = $this.find('.sgu_fw-enabled'),
          $has_disabled = $this.find('.sgu_fw-disabled'),
          $disabled     = ( $has_disabled.length ) ? $has_disabled : false;

      $enabled.sortable({
        connectWith: $disabled,
        placeholder: 'ui-sortable-placeholder',
        update: function( event, ui ) {

          var $el = ui.item.find('input');

          if ( ui.item.parent().hasClass('sgu_fw-enabled') ) {
            $el.attr('name', $el.attr('name').replace('disabled', 'enabled'));
          } else {
            $el.attr('name', $el.attr('name').replace('enabled', 'disabled'));
          }

          $this.sgu_fw_customizer_refresh();

        }
      });

      if ( $disabled ) {

        $disabled.sortable({
          connectWith: $enabled,
          placeholder: 'ui-sortable-placeholder',
          update: function( event, ui ) {
            $this.sgu_fw_customizer_refresh();
          }
        });

      }

    });
  };

  //
  // Field: spinner
  //
  $.fn.sgu_fw_field_spinner = function() {
    return this.each( function() {

      var $this   = $(this),
          $input  = $this.find('input'),
          $inited = $this.find('.ui-button'),
          data    = $input.data();

      if ( $inited.length ) {
        $inited.remove();
      }

      $input.spinner({
        min: data.min || 0,
        max: data.max || 100,
        step: data.step || 1,
        create: function( event, ui ) {
          if ( data.unit ) {
            $input.after('<span class="ui-button sgu_fw--unit">'+ data.unit +'</span>');
          }
        },
        spin: function (event, ui ) {
          $input.val(ui.value).trigger('change');
        }
      });

    });
  };

  //
  // Field: switcher
  //
  $.fn.sgu_fw_field_switcher = function() {
    return this.each( function() {

      var $switcher = $(this).find('.sgu_fw--switcher');

      $switcher.on('click', function() {

        var value  = 0;
        var $input = $switcher.find('input');

        if ( $switcher.hasClass('sgu_fw--active') ) {
          $switcher.removeClass('sgu_fw--active');
        } else {
          value = 1;
          $switcher.addClass('sgu_fw--active');
        }

        $input.val(value).trigger('change');

      });

    });
  };

  //
  // Field: tabbed
  //
  $.fn.sgu_fw_field_tabbed = function() {
    return this.each( function() {

      var $this     = $(this),
          $links    = $this.find('.sgu_fw-tabbed-nav a'),
          $contents = $this.find('.sgu_fw-tabbed-content');

      $contents.eq(0).sgu_fw_reload_script();

      $links.on( 'click', function( e ) {

       e.preventDefault();

        var $link    = $(this),
            index    = $link.index(),
            $content = $contents.eq(index);

        $link.addClass('sgu_fw-tabbed-active').siblings().removeClass('sgu_fw-tabbed-active');
        $content.sgu_fw_reload_script();
        $content.removeClass('hidden').siblings().addClass('hidden');

      });

    });
  };

  //
  // Field: typography
  //
  $.fn.sgu_fw_field_typography = function() {
    return this.each(function () {

      var base          = this;
      var $this         = $(this);
      var loaded_fonts  = [];
      var webfonts      = sgu_fw_typography_json.webfonts;
      var googlestyles  = sgu_fw_typography_json.googlestyles;
      var defaultstyles = sgu_fw_typography_json.defaultstyles;

      //
      //
      // Sanitize google font subset
      base.sanitize_subset = function( subset ) {
        subset = subset.replace('-ext', ' Extended');
        subset = subset.charAt(0).toUpperCase() + subset.slice(1);
        return subset;
      };

      //
      //
      // Sanitize google font styles (weight and style)
      base.sanitize_style = function( style ) {
        return googlestyles[style] ? googlestyles[style] : style;
      };

      //
      //
      // Load google font
      base.load_google_font = function( font_family, weight, style ) {

        if ( font_family && typeof WebFont === 'object' ) {

          weight = weight ? weight.replace('normal', '') : '';
          style  = style ? style.replace('normal', '') : '';

          if ( weight || style ) {
            font_family = font_family +':'+ weight + style;
          }

          if ( loaded_fonts.indexOf( font_family ) === -1 ) {
            WebFont.load({ google: { families: [font_family] } });
          }

          loaded_fonts.push( font_family );

        }

      };

      //
      //
      // Append select options
      base.append_select_options = function( $select, options, condition, type, is_multi ) {

        $select.find('option').not(':first').remove();

        var opts = '';

        $.each( options, function( key, value ) {

          var selected;
          var name = value;

          // is_multi
          if ( is_multi ) {
            selected = ( condition && condition.indexOf(value) !== -1 ) ? ' selected' : '';
          } else {
            selected = ( condition && condition === value ) ? ' selected' : '';
          }

          if ( type === 'subset' ) {
            name = base.sanitize_subset( value );
          } else if ( type === 'style' ){
            name = base.sanitize_style( value );
          }

          opts += '<option value="'+ value +'"'+ selected +'>'+ name +'</option>';

        });

        $select.append(opts).trigger('sgu_fw.change').trigger('chosen:updated');

      };

      base.init = function () {

        //
        //
        // Constants
        var selected_styles  = [];
        var $typography      = $this.find('.sgu_fw--typography');
        var $type            = $this.find('.sgu_fw--type');
        var $styles          = $this.find('.sgu_fw--block-font-style');
        var unit             = $typography.data('unit');
        var line_height_unit = $typography.data('line-height-unit');
        var exclude_fonts    = $typography.data('exclude') ? $typography.data('exclude').split(',') : [];

        //
        //
        // Chosen init
        if ( $this.find('.sgu_fw--chosen').length ) {

          var $chosen_selects = $this.find('select');

          $chosen_selects.each( function() {

            var $chosen_select = $(this),
                $chosen_inited = $chosen_select.parent().find('.chosen-container');

            if ( $chosen_inited.length ) {
              $chosen_inited.remove();
            }

            $chosen_select.chosen({
              allow_single_deselect: true,
              disable_search_threshold: 15,
              width: '100%'
            });

          });

        }

        //
        //
        // Font family select
        var $font_family_select = $this.find('.sgu_fw--font-family');
        var first_font_family   = $font_family_select.val();

        // Clear default font family select options
        $font_family_select.find('option').not(':first-child').remove();

        var opts = '';

        $.each(webfonts, function( type, group ) {

          // Check for exclude fonts
          if ( exclude_fonts && exclude_fonts.indexOf(type) !== -1 ) { return; }

          opts += '<optgroup label="' + group.label + '">';

          $.each(group.fonts, function( key, value ) {

            // use key if value is object
            value = ( typeof value === 'object' ) ? key : value;
            var selected = ( value === first_font_family ) ? ' selected' : '';
            opts += '<option value="'+ value +'" data-type="'+ type +'"'+ selected +'>'+ value +'</option>';

          });

          opts += '</optgroup>';

        });

        // Append google font select options
        $font_family_select.append(opts).trigger('chosen:updated');

        //
        //
        // Font style select
        var $font_style_block = $this.find('.sgu_fw--block-font-style');

        if ( $font_style_block.length ) {

          var $font_style_select = $this.find('.sgu_fw--font-style-select');
          var first_style_value  = $font_style_select.val() ? $font_style_select.val().replace(/normal/g, '' ) : '';

          //
          // Font Style on on change listener
          $font_style_select.on('change sgu_fw.change', function( event ) {

            var style_value = $font_style_select.val();

            // set a default value
            if ( !style_value && selected_styles && selected_styles.indexOf('normal') === -1 ) {
              style_value = selected_styles[0];
            }

            // set font weight, for eg. replacing 800italic to 800
            var font_normal = ( style_value && style_value !== 'italic' && style_value === 'normal' ) ? 'normal' : '';
            var font_weight = ( style_value && style_value !== 'italic' && style_value !== 'normal' ) ? style_value.replace('italic', '') : font_normal;
            var font_style  = ( style_value && style_value.substr(-6) === 'italic' ) ? 'italic' : '';

            $this.find('.sgu_fw--font-weight').val( font_weight );
            $this.find('.sgu_fw--font-style').val( font_style );

          });

          //
          //
          // Extra font style select
          var $extra_font_style_block = $this.find('.sgu_fw--block-extra-styles');

          if ( $extra_font_style_block.length ) {
            var $extra_font_style_select = $this.find('.sgu_fw--extra-styles');
            var first_extra_style_value  = $extra_font_style_select.val();
          }

        }

        //
        //
        // Subsets select
        var $subset_block = $this.find('.sgu_fw--block-subset');
        if ( $subset_block.length ) {
          var $subset_select = $this.find('.sgu_fw--subset');
          var first_subset_select_value = $subset_select.val();
          var subset_multi_select = $subset_select.data('multiple') || false;
        }

        //
        //
        // Backup font family
        var $backup_font_family_block = $this.find('.sgu_fw--block-backup-font-family');

        //
        //
        // Font Family on Change Listener
        $font_family_select.on('change sgu_fw.change', function( event ) {

          // Hide subsets on change
          if ( $subset_block.length ) {
            $subset_block.addClass('hidden');
          }

          // Hide extra font style on change
          if ( $extra_font_style_block.length ) {
            $extra_font_style_block.addClass('hidden');
          }

          // Hide backup font family on change
          if ( $backup_font_family_block.length ) {
            $backup_font_family_block.addClass('hidden');
          }

          var $selected = $font_family_select.find(':selected');
          var value     = $selected.val();
          var type      = $selected.data('type');

          if ( type && value ) {

            // Show backup fonts if font type google or custom
            if ( ( type === 'google' || type === 'custom' ) && $backup_font_family_block.length ) {
              $backup_font_family_block.removeClass('hidden');
            }

            // Appending font style select options
            if ( $font_style_block.length ) {

              // set styles for multi and normal style selectors
              var styles = defaultstyles;

              // Custom or gogle font styles
              if ( type === 'google' && webfonts[type].fonts[value][0] ) {
                styles = webfonts[type].fonts[value][0];
              } else if ( type === 'custom' && webfonts[type].fonts[value] ) {
                styles = webfonts[type].fonts[value];
              }

              selected_styles = styles;

              // Set selected style value for avoid load errors
              var set_auto_style  = ( styles.indexOf('normal') !== -1 ) ? 'normal' : styles[0];
              var set_style_value = ( first_style_value && styles.indexOf(first_style_value) !== -1 ) ? first_style_value : set_auto_style;

              // Append style select options
              base.append_select_options( $font_style_select, styles, set_style_value, 'style' );

              // Clear first value
              first_style_value = false;

              // Show style select after appended
              $font_style_block.removeClass('hidden');

              // Appending extra font style select options
              if ( type === 'google' && $extra_font_style_block.length && styles.length > 1 ) {

                // Append extra-style select options
                base.append_select_options( $extra_font_style_select, styles, first_extra_style_value, 'style', true );

                // Clear first value
                first_extra_style_value = false;

                // Show style select after appended
                $extra_font_style_block.removeClass('hidden');

              }

            }

            // Appending google fonts subsets select options
            if ( type === 'google' && $subset_block.length && webfonts[type].fonts[value][1] ) {

              var subsets          = webfonts[type].fonts[value][1];
              var set_auto_subset  = ( subsets.length < 2 && subsets[0] !== 'latin' ) ? subsets[0] : '';
              var set_subset_value = ( first_subset_select_value && subsets.indexOf(first_subset_select_value) !== -1 ) ? first_subset_select_value : set_auto_subset;

              // check for multiple subset select
              set_subset_value = ( subset_multi_select && first_subset_select_value ) ? first_subset_select_value : set_subset_value;

              base.append_select_options( $subset_select, subsets, set_subset_value, 'subset', subset_multi_select );

              first_subset_select_value = false;

              $subset_block.removeClass('hidden');

            }

          } else {

            // Clear Styles
            $styles.find(':input').val('');

            // Clear subsets options if type and value empty
            if ( $subset_block.length ) {
              $subset_select.find('option').not(':first-child').remove();
              $subset_select.trigger('chosen:updated');
            }

            // Clear font styles options if type and value empty
            if ( $font_style_block.length ) {
              $font_style_select.find('option').not(':first-child').remove();
              $font_style_select.trigger('chosen:updated');
            }

          }

          // Update font type input value
          $type.val(type);

        }).trigger('sgu_fw.change');

        //
        //
        // Preview
        var $preview_block = $this.find('.sgu_fw--block-preview');

        if ( $preview_block.length ) {

          var $preview = $this.find('.sgu_fw--preview');

          // Set preview styles on change
          $this.on('change', SGU_FW.helper.debounce( function( event ) {

            $preview_block.removeClass('hidden');

            var font_family       = $font_family_select.val(),
                font_weight       = $this.find('.sgu_fw--font-weight').val(),
                font_style        = $this.find('.sgu_fw--font-style').val(),
                font_size         = $this.find('.sgu_fw--font-size').val(),
                font_variant      = $this.find('.sgu_fw--font-variant').val(),
                line_height       = $this.find('.sgu_fw--line-height').val(),
                text_align        = $this.find('.sgu_fw--text-align').val(),
                text_transform    = $this.find('.sgu_fw--text-transform').val(),
                text_decoration   = $this.find('.sgu_fw--text-decoration').val(),
                text_color        = $this.find('.sgu_fw--color').val(),
                word_spacing      = $this.find('.sgu_fw--word-spacing').val(),
                letter_spacing    = $this.find('.sgu_fw--letter-spacing').val(),
                custom_style      = $this.find('.sgu_fw--custom-style').val(),
                type              = $this.find('.sgu_fw--type').val();

            if ( type === 'google' ) {
              base.load_google_font(font_family, font_weight, font_style);
            }

            var properties = {};

            if ( font_family     ) { properties.fontFamily     = font_family;                    }
            if ( font_weight     ) { properties.fontWeight     = font_weight;                    }
            if ( font_style      ) { properties.fontStyle      = font_style;                     }
            if ( font_variant    ) { properties.fontVariant    = font_variant;                   }
            if ( font_size       ) { properties.fontSize       = font_size + unit;               }
            if ( line_height     ) { properties.lineHeight     = line_height + line_height_unit; }
            if ( letter_spacing  ) { properties.letterSpacing  = letter_spacing + unit;          }
            if ( word_spacing    ) { properties.wordSpacing    = word_spacing + unit;            }
            if ( text_align      ) { properties.textAlign      = text_align;                     }
            if ( text_transform  ) { properties.textTransform  = text_transform;                 }
            if ( text_decoration ) { properties.textDecoration = text_decoration;                }
            if ( text_color      ) { properties.color          = text_color;                     }

            $preview.removeAttr('style');

            // Customs style attribute
            if ( custom_style ) { $preview.attr('style', custom_style); }

            $preview.css(properties);

          }, 100 ) );

          // Preview black and white backgrounds trigger
          $preview_block.on('click', function() {

            $preview.toggleClass('sgu_fw--black-background');

            var $toggle = $preview_block.find('.sgu_fw--toggle');

            if ( $toggle.hasClass('fa-toggle-off') ) {
              $toggle.removeClass('fa-toggle-off').addClass('fa-toggle-on');
            } else {
              $toggle.removeClass('fa-toggle-on').addClass('fa-toggle-off');
            }

          });

          if ( !$preview_block.hasClass('hidden') ) {
            $this.trigger('change');
          }

        }

      };

      base.init();

    });
  };

  //
  // Field: upload
  //
  $.fn.sgu_fw_field_upload = function() {
    return this.each( function() {

      var $this          = $(this),
          $input         = $this.find('input'),
          $upload_button = $this.find('.sgu_fw--button'),
          $remove_button = $this.find('.sgu_fw--remove'),
          $preview_wrap  = $this.find('.sgu_fw--preview'),
          $preview_src   = $this.find('.sgu_fw--src'),
          $library       = $upload_button.data('library') && $upload_button.data('library').split(',') || '',
          wp_media_frame;

      $upload_button.on('click', function( e ) {

        e.preventDefault();

        if ( typeof window.wp === 'undefined' || ! window.wp.media || ! window.wp.media.gallery ) {
          return;
        }

        if ( wp_media_frame ) {
          wp_media_frame.open();
          return;
        }

        wp_media_frame = window.wp.media({
          library: {
            type: $library
          },
        });

        wp_media_frame.on( 'select', function() {

          var src;
          var attributes = wp_media_frame.state().get('selection').first().attributes;

          if ( $library.length && $library.indexOf(attributes.subtype) === -1 && $library.indexOf(attributes.type) === -1 ) {
            return;
          }

          $input.val(attributes.url).trigger('change');

        });

        wp_media_frame.open();

      });

      $remove_button.on('click', function( e ) {
        e.preventDefault();
        $input.val('').trigger('change');
      });

      $input.on('change', function( e ) {

        var $value = $input.val();

        if ( $value ) {
          $remove_button.removeClass('hidden');
        } else {
          $remove_button.addClass('hidden');
        }

        if ( $preview_wrap.length ) {

          if ( $.inArray( $value.split('.').pop().toLowerCase(), ['jpg', 'jpeg', 'gif', 'png', 'svg', 'webp'] ) !== -1 ) {
            $preview_wrap.removeClass('hidden');
            $preview_src.attr('src', $value);
          } else {
            $preview_wrap.addClass('hidden');
          }

        }

      });

    });

  };

  //
  // Field: wp_editor
  //
  $.fn.sgu_fw_field_wp_editor = function() {
    return this.each( function() {

      if ( typeof window.wp.editor === 'undefined' || typeof window.tinyMCEPreInit === 'undefined' || typeof window.tinyMCEPreInit.mceInit.sgu_fw_wp_editor === 'undefined' ) {
        return;
      }

      var $this     = $(this),
          $editor   = $this.find('.sgu_fw-wp-editor'),
          $textarea = $this.find('textarea');

      // If there is wp-editor remove it for avoid dupliated wp-editor conflicts.
      var $has_wp_editor = $this.find('.wp-editor-wrap').length || $this.find('.mce-container').length;

      if ( $has_wp_editor ) {
        $editor.empty();
        $editor.append($textarea);
        $textarea.css('display', '');
      }

      // Generate a unique id
      var uid = SGU_FW.helper.uid('sgu_fw-editor-');

      $textarea.attr('id', uid);

      // Get default editor settings
      var default_editor_settings = {
        tinymce: window.tinyMCEPreInit.mceInit.sgu_fw_wp_editor,
        quicktags: window.tinyMCEPreInit.qtInit.sgu_fw_wp_editor
      };

      // Get default editor settings
      var field_editor_settings = $editor.data('editor-settings');

      // Callback for old wp editor
      var wpEditor = wp.oldEditor ? wp.oldEditor : wp.editor;

      if ( wpEditor && wpEditor.hasOwnProperty('autop') ) {
        wp.editor.autop = wpEditor.autop;
        wp.editor.removep = wpEditor.removep;
        wp.editor.initialize = wpEditor.initialize;
      }

      // Add on change event handle
      var editor_on_change = function( editor ) {
        editor.on('change keyup', function() {
          var value = ( field_editor_settings.wpautop ) ? editor.getContent() : wp.editor.removep(editor.getContent());
          $textarea.val(value).trigger('change');
        });
      };

      // Extend editor selector and on change event handler
      default_editor_settings.tinymce = $.extend( {}, default_editor_settings.tinymce, { selector: '#'+ uid, setup: editor_on_change } );

      // Override editor tinymce settings
      if ( field_editor_settings.tinymce === false ) {
        default_editor_settings.tinymce = false;
        $editor.addClass('sgu_fw-no-tinymce');
      }

      // Override editor quicktags settings
      if ( field_editor_settings.quicktags === false ) {
        default_editor_settings.quicktags = false;
        $editor.addClass('sgu_fw-no-quicktags');
      }

      // Wait until :visible
      var interval = setInterval(function () {
        if ( $this.is(':visible') ) {
          window.wp.editor.initialize(uid, default_editor_settings);
          clearInterval(interval);
        }
      });

      // Add Media buttons
      if ( field_editor_settings.media_buttons && window.sgu_fw_media_buttons ) {

        var $editor_buttons = $editor.find('.wp-media-buttons');

        if ( $editor_buttons.length ) {

          $editor_buttons.find('.sgu_fw-shortcode-button').data('editor-id', uid);

        } else {

          var $media_buttons = $(window.sgu_fw_media_buttons);

          $media_buttons.find('.sgu_fw-shortcode-button').data('editor-id', uid);

          $editor.prepend( $media_buttons );

        }

      }

    });

  };

  //
  // Confirm
  //
  $.fn.sgu_fw_confirm = function() {
    return this.each( function() {
      $(this).on('click', function( e ) {

        var confirm_text   = $(this).data('confirm') || window.sgu_fw_vars.i18n.confirm;
        var confirm_answer = confirm( confirm_text );

        if ( confirm_answer ) {
          SGU_FW.vars.is_confirm = true;
          SGU_FW.vars.form_modified = false;
        } else {
          e.preventDefault();
          return false;
        }

      });
    });
  };

  $.fn.serializeObject = function() {

    var obj = {};

    $.each( this.serializeArray(), function(i,o){
      var n = o.name,
        v = o.value;

        obj[n] = obj[n] === undefined ? v
          : $.isArray( obj[n] ) ? obj[n].concat( v )
          : [ obj[n], v ];
    });

    return obj;

  };

  //
  // Options Save
  //
  $.fn.sgu_fw_save = function() {
    return this.each( function() {

      var $this    = $(this),
          $buttons = $('.sgu_fw-save'),
          $panel   = $('.sgu_fw-options'),
          flooding = false,
          timeout;

      $this.on('click', function( e ) {

        if ( !flooding ) {

          var $text  = $this.data('save'),
              $value = $this.val();

          $buttons.attr('value', $text);

          if ( $this.hasClass('sgu_fw-save-ajax') ) {

            e.preventDefault();

            $panel.addClass('sgu_fw-saving');
            $buttons.prop('disabled', true);

            window.wp.ajax.post( 'sgu_fw_'+ $panel.data('unique') +'_ajax_save', {
              data: $('#sgu_fw-form').serializeJSONSGU_FW()
            })
            .done( function( response ) {

              // clear errors
              $('.sgu_fw-error').remove();

              if ( Object.keys( response.errors ).length ) {

                var error_icon = '<i class="sgu_fw-label-error sgu_fw-error">!</i>';

                $.each(response.errors, function( key, error_message ) {

                  var $field = $('[data-depend-id="'+ key +'"]'),
                      $link  = $('a[href="#tab='+ $field.closest('.sgu_fw-section').data('section-id') +'"]' ),
                      $tab   = $link.closest('.sgu_fw-tab-item');

                  $field.closest('.sgu_fw-fieldset').append( '<p class="sgu_fw-error sgu_fw-error-text">'+ error_message +'</p>' );

                  if ( !$link.find('.sgu_fw-error').length ) {
                    $link.append( error_icon );
                  }

                  if ( !$tab.find('.sgu_fw-arrow .sgu_fw-error').length ) {
                    $tab.find('.sgu_fw-arrow').append( error_icon );
                  }

                });

              }

              $panel.removeClass('sgu_fw-saving');
              $buttons.prop('disabled', false).attr('value', $value);
              flooding = false;

              SGU_FW.vars.form_modified = false;
              SGU_FW.vars.$form_warning.hide();

              clearTimeout(timeout);

              var $result_success = $('.sgu_fw-form-success');
              $result_success.empty().append(response.notice).fadeIn('fast', function() {
                timeout = setTimeout( function() {
                  $result_success.fadeOut('fast');
                }, 1000);
              });

            })
            .fail( function( response ) {
              alert( response.error );
            });

          } else {

            SGU_FW.vars.form_modified = false;

          }

        }

        flooding = true;

      });

    });
  };

  //
  // Option Framework
  //
  $.fn.sgu_fw_options = function() {
    return this.each( function() {

      var $this         = $(this),
          $content      = $this.find('.sgu_fw-content'),
          $form_success = $this.find('.sgu_fw-form-success'),
          $form_warning = $this.find('.sgu_fw-form-warning'),
          $save_button  = $this.find('.sgu_fw-header .sgu_fw-save');

      SGU_FW.vars.$form_warning = $form_warning;

      // Shows a message white leaving theme options without saving
      if ( $form_warning.length ) {

        window.onbeforeunload = function() {
          return ( SGU_FW.vars.form_modified ) ? true : undefined;
        };

        $content.on('change keypress', ':input', function() {
          if ( !SGU_FW.vars.form_modified ) {
            $form_success.hide();
            $form_warning.fadeIn('fast');
            SGU_FW.vars.form_modified = true;
          }
        });

      }

      if ( $form_success.hasClass('sgu_fw-form-show') ) {
        setTimeout( function() {
          $form_success.fadeOut('fast');
        }, 1000);
      }

      $(document).keydown(function (event) {
        if ( ( event.ctrlKey || event.metaKey ) && event.which === 83 ) {
          $save_button.trigger('click');
          event.preventDefault();
          return false;
        }
      });

    });
  };

  //
  // Taxonomy Framework
  //
  $.fn.sgu_fw_taxonomy = function() {
    return this.each( function() {

      var $this = $(this),
          $form = $this.parents('form');

      if ( $form.attr('id') === 'addtag' ) {

        var $submit = $form.find('#submit'),
            $cloned = $this.children('.sgu_fw-field').sgu_fw_clone();

        $submit.on( 'click', function() {

          if ( !$form.find('.form-required').hasClass('form-invalid') ) {

            $this.data('inited', false);

            $this.empty();

            $this.html( $cloned );

            $cloned = $cloned.sgu_fw_clone();

            $this.sgu_fw_reload_script();

          }

        });

      }

    });
  };

  //
  // Shortcode Framework
  //
  $.fn.sgu_fw_shortcode = function() {

    var base = this;

    base.shortcode_parse = function( serialize, key ) {

      var shortcode = '';

      $.each(serialize, function( shortcode_key, shortcode_values ) {

        key = ( key ) ? key : shortcode_key;

        shortcode += '[' + key;

        $.each(shortcode_values, function( shortcode_tag, shortcode_value ) {

          if ( shortcode_tag === 'content' ) {

            shortcode += ']';
            shortcode += shortcode_value;
            shortcode += '[/'+ key +'';

          } else {

            shortcode += base.shortcode_tags( shortcode_tag, shortcode_value );

          }

        });

        shortcode += ']';

      });

      return shortcode;

    };

    base.shortcode_tags = function( shortcode_tag, shortcode_value ) {

      var shortcode = '';

      if ( shortcode_value !== '' ) {

        if ( typeof shortcode_value === 'object' && !$.isArray( shortcode_value ) ) {

          $.each(shortcode_value, function( sub_shortcode_tag, sub_shortcode_value ) {

            // sanitize spesific key/value
            switch( sub_shortcode_tag ) {

              case 'background-image':
                sub_shortcode_value = ( sub_shortcode_value.url  ) ? sub_shortcode_value.url : '';
              break;

            }

            if ( sub_shortcode_value !== '' ) {
              shortcode += ' ' + sub_shortcode_tag + '="' + sub_shortcode_value.toString() + '"';
            }

          });

        } else {

          shortcode += ' ' + shortcode_tag + '="' + shortcode_value.toString() + '"';

        }

      }

      return shortcode;

    };

    base.insertAtChars = function( _this, currentValue ) {

      var obj = ( typeof _this[0].name !== 'undefined' ) ? _this[0] : _this;

      if ( obj.value.length && typeof obj.selectionStart !== 'undefined' ) {
        obj.focus();
        return obj.value.substring( 0, obj.selectionStart ) + currentValue + obj.value.substring( obj.selectionEnd, obj.value.length );
      } else {
        obj.focus();
        return currentValue;
      }

    };

    base.send_to_editor = function( html, editor_id ) {

      var tinymce_editor;

      if ( typeof tinymce !== 'undefined' ) {
        tinymce_editor = tinymce.get( editor_id );
      }

      if ( tinymce_editor && !tinymce_editor.isHidden() ) {
        tinymce_editor.execCommand( 'mceInsertContent', false, html );
      } else {
        var $editor = $('#'+editor_id);
        $editor.val( base.insertAtChars( $editor, html ) ).trigger('change');
      }

    };

    return this.each( function() {

      var $modal   = $(this),
          $load    = $modal.find('.sgu_fw-modal-load'),
          $content = $modal.find('.sgu_fw-modal-content'),
          $insert  = $modal.find('.sgu_fw-modal-insert'),
          $loading = $modal.find('.sgu_fw-modal-loading'),
          $select  = $modal.find('select'),
          modal_id = $modal.data('modal-id'),
          nonce    = $modal.data('nonce'),
          editor_id,
          target_id,
          gutenberg_id,
          sc_key,
          sc_name,
          sc_view,
          sc_group,
          $cloned,
          $button;

      $(document).on('click', '.sgu_fw-shortcode-button[data-modal-id="'+ modal_id +'"]', function( e ) {

        e.preventDefault();

        $button      = $(this);
        editor_id    = $button.data('editor-id')    || false;
        target_id    = $button.data('target-id')    || false;
        gutenberg_id = $button.data('gutenberg-id') || false;

        $modal.removeClass('hidden');

        // single usage trigger first shortcode
        if ( $modal.hasClass('sgu_fw-shortcode-single') && sc_name === undefined ) {
          $select.trigger('change');
        }

      });

      $select.on( 'change', function() {

        var $option   = $(this);
        var $selected = $option.find(':selected');

        sc_key   = $option.val();
        sc_name  = $selected.data('shortcode');
        sc_view  = $selected.data('view') || 'normal';
        sc_group = $selected.data('group') || sc_name;

        $load.empty();

        if ( sc_key ) {

          $loading.show();

          window.wp.ajax.post( 'sgu_fw-get-shortcode-'+ modal_id, {
            shortcode_key: sc_key,
            nonce: nonce
          })
          .done( function( response ) {

            $loading.hide();

            var $appended = $(response.content).appendTo($load);

            $insert.parent().removeClass('hidden');

            $cloned = $appended.find('.sgu_fw--repeat-shortcode').sgu_fw_clone();

            $appended.sgu_fw_reload_script();
            $appended.find('.sgu_fw-fields').sgu_fw_reload_script();

          });

        } else {

          $insert.parent().addClass('hidden');

        }

      });

      $insert.on('click', function( e ) {

        e.preventDefault();

        if ( $insert.prop('disabled') || $insert.attr('disabled') ) { return; }

        var shortcode = '';
        var serialize = $modal.find('.sgu_fw-field:not(.sgu_fw-depend-on)').find(':input:not(.ignore)').serializeObjectSGU_FW();

        switch ( sc_view ) {

          case 'contents':
            var contentsObj = ( sc_name ) ? serialize[sc_name] : serialize;
            $.each(contentsObj, function( sc_key, sc_value ) {
              var sc_tag = ( sc_name ) ? sc_name : sc_key;
              shortcode += '['+ sc_tag +']'+ sc_value +'[/'+ sc_tag +']';
            });
          break;

          case 'group':

            shortcode += '[' + sc_name;
            $.each(serialize[sc_name], function( sc_key, sc_value ) {
              shortcode += base.shortcode_tags( sc_key, sc_value );
            });
            shortcode += ']';
            shortcode += base.shortcode_parse( serialize[sc_group], sc_group );
            shortcode += '[/' + sc_name + ']';

          break;

          case 'repeater':
            shortcode += base.shortcode_parse( serialize[sc_group], sc_group );
          break;

          default:
            shortcode += base.shortcode_parse( serialize );
          break;

        }

        shortcode = ( shortcode === '' ) ? '['+ sc_name +']' : shortcode;

        if ( gutenberg_id ) {

          var content = window.sgu_fw_gutenberg_props.attributes.hasOwnProperty('shortcode') ? window.sgu_fw_gutenberg_props.attributes.shortcode : '';
          window.sgu_fw_gutenberg_props.setAttributes({shortcode: content + shortcode});

        } else if ( editor_id ) {

          base.send_to_editor( shortcode, editor_id );

        } else {

          var $textarea = (target_id) ? $(target_id) : $button.parent().find('textarea');
          $textarea.val( base.insertAtChars( $textarea, shortcode ) ).trigger('change');

        }

        $modal.addClass('hidden');

      });

      $modal.on('click', '.sgu_fw--repeat-button', function( e ) {

        e.preventDefault();

        var $repeatable = $modal.find('.sgu_fw--repeatable');
        var $new_clone  = $cloned.sgu_fw_clone();
        var $remove_btn = $new_clone.find('.sgu_fw-repeat-remove');

        var $appended = $new_clone.appendTo( $repeatable );

        $new_clone.find('.sgu_fw-fields').sgu_fw_reload_script();

        SGU_FW.helper.name_nested_replace( $modal.find('.sgu_fw--repeat-shortcode'), sc_group );

        $remove_btn.on('click', function() {

          $new_clone.remove();

          SGU_FW.helper.name_nested_replace( $modal.find('.sgu_fw--repeat-shortcode'), sc_group );

        });

      });

      $modal.on('click', '.sgu_fw-modal-close, .sgu_fw-modal-overlay', function() {
        $modal.addClass('hidden');
      });

    });
  };

  //
  // WP Color Picker
  //
  if ( typeof Color === 'function' ) {

    Color.prototype.toString = function() {

      if ( this._alpha < 1 ) {
        return this.toCSS('rgba', this._alpha).replace(/\s+/g, '');
      }

      var hex = parseInt( this._color, 10 ).toString( 16 );

      if ( this.error ) { return ''; }

      if ( hex.length < 6 ) {
        for (var i = 6 - hex.length - 1; i >= 0; i--) {
          hex = '0' + hex;
        }
      }

      return '#' + hex;

    };

  }

  SGU_FW.funcs.parse_color = function( color ) {

    var value = color.replace(/\s+/g, ''),
        trans = ( value.indexOf('rgba') !== -1 ) ? parseFloat( value.replace(/^.*,(.+)\)/, '$1') * 100 ) : 100,
        rgba  = ( trans < 100 ) ? true : false;

    return { value: value, transparent: trans, rgba: rgba };

  };

  $.fn.sgu_fw_color = function() {
    return this.each( function() {

      var $input        = $(this),
          picker_color  = SGU_FW.funcs.parse_color( $input.val() ),
          palette_color = window.sgu_fw_vars.color_palette.length ? window.sgu_fw_vars.color_palette : true,
          $container;

      // Destroy and Reinit
      if ( $input.hasClass('wp-color-picker') ) {
        $input.closest('.wp-picker-container').after($input).remove();
      }

      $input.wpColorPicker({
        palettes: palette_color,
        change: function( event, ui ) {

          var ui_color_value = ui.color.toString();

          $container.removeClass('sgu_fw--transparent-active');
          $container.find('.sgu_fw--transparent-offset').css('background-color', ui_color_value);
          $input.val(ui_color_value).trigger('change');

        },
        create: function() {

          $container = $input.closest('.wp-picker-container');

          var a8cIris = $input.data('a8cIris'),
              $transparent_wrap = $('<div class="sgu_fw--transparent-wrap">' +
                                '<div class="sgu_fw--transparent-slider"></div>' +
                                '<div class="sgu_fw--transparent-offset"></div>' +
                                '<div class="sgu_fw--transparent-text"></div>' +
                                '<div class="sgu_fw--transparent-button">transparent <i class="fas fa-toggle-off"></i></div>' +
                                '</div>').appendTo( $container.find('.wp-picker-holder') ),
              $transparent_slider = $transparent_wrap.find('.sgu_fw--transparent-slider'),
              $transparent_text   = $transparent_wrap.find('.sgu_fw--transparent-text'),
              $transparent_offset = $transparent_wrap.find('.sgu_fw--transparent-offset'),
              $transparent_button = $transparent_wrap.find('.sgu_fw--transparent-button');

          if ( $input.val() === 'transparent' ) {
            $container.addClass('sgu_fw--transparent-active');
          }

          $transparent_button.on('click', function() {
            if ( $input.val() !== 'transparent' ) {
              $input.val('transparent').trigger('change').removeClass('iris-error');
              $container.addClass('sgu_fw--transparent-active');
            } else {
              $input.val( a8cIris._color.toString() ).trigger('change');
              $container.removeClass('sgu_fw--transparent-active');
            }
          });

          $transparent_slider.slider({
            value: picker_color.transparent,
            step: 1,
            min: 0,
            max: 100,
            slide: function( event, ui ) {

              var slide_value = parseFloat( ui.value / 100 );
              a8cIris._color._alpha = slide_value;
              $input.wpColorPicker( 'color', a8cIris._color.toString() );
              $transparent_text.text( ( slide_value === 1 || slide_value === 0 ? '' : slide_value ) );

            },
            create: function() {

              var slide_value = parseFloat( picker_color.transparent / 100 ),
                  text_value  = slide_value < 1 ? slide_value : '';

              $transparent_text.text(text_value);
              $transparent_offset.css('background-color', picker_color.value);

              $container.on('click', '.wp-picker-clear', function() {

                a8cIris._color._alpha = 1;
                $transparent_text.text('');
                $transparent_slider.slider('option', 'value', 100);
                $container.removeClass('sgu_fw--transparent-active');
                $input.trigger('change');

              });

              $container.on('click', '.wp-picker-default', function() {

                var default_color = SGU_FW.funcs.parse_color( $input.data('default-color') ),
                    default_value = parseFloat( default_color.transparent / 100 ),
                    default_text  = default_value < 1 ? default_value : '';

                a8cIris._color._alpha = default_value;
                $transparent_text.text(default_text);
                $transparent_slider.slider('option', 'value', default_color.transparent);

                if ( default_color.value === 'transparent' ) {
                  $input.removeClass('iris-error');
                  $container.addClass('sgu_fw--transparent-active');
                }

              });

            }
          });
        }
      });

    });
  };

  //
  // ChosenJS
  //
  $.fn.sgu_fw_chosen = function() {
    return this.each( function() {

      var $this       = $(this),
          $inited     = $this.parent().find('.chosen-container'),
          is_sortable = $this.hasClass('sgu_fw-chosen-sortable') || false,
          is_ajax     = $this.hasClass('sgu_fw-chosen-ajax') || false,
          is_multiple = $this.attr('multiple') || false,
          set_width   = is_multiple ? '100%' : 'auto',
          set_options = $.extend({
            allow_single_deselect: true,
            disable_search_threshold: 10,
            width: set_width,
            no_results_text: window.sgu_fw_vars.i18n.no_results_text,
          }, $this.data('chosen-settings'));

      if ( $inited.length ) {
        $inited.remove();
      }

      // Chosen ajax
      if ( is_ajax ) {

        var set_ajax_options = $.extend({
          data: {
            type: 'post',
            nonce: '',
          },
          allow_single_deselect: true,
          disable_search_threshold: -1,
          width: '100%',
          min_length: 3,
          type_delay: 500,
          typing_text: window.sgu_fw_vars.i18n.typing_text,
          searching_text: window.sgu_fw_vars.i18n.searching_text,
          no_results_text: window.sgu_fw_vars.i18n.no_results_text,
        }, $this.data('chosen-settings'));

        $this.SGU_FWAjaxChosen(set_ajax_options);

      } else {

        $this.chosen(set_options);

      }

      // Chosen keep options order
      if ( is_multiple ) {

        var $hidden_select = $this.parent().find('.sgu_fw-hide-select');
        var $hidden_value  = $hidden_select.val() || [];

        $this.on('change', function(obj, result) {

          if ( result && result.selected ) {
            $hidden_select.append( '<option value="'+ result.selected +'" selected="selected">'+ result.selected +'</option>' );
          } else if ( result && result.deselected ) {
            $hidden_select.find('option[value="'+ result.deselected +'"]').remove();
          }

          // Force customize refresh
          if ( window.wp.customize !== undefined && $hidden_select.children().length === 0 && $hidden_select.data('customize-setting-link') ) {
            window.wp.customize.control( $hidden_select.data('customize-setting-link') ).setting.set('');
          }

          $hidden_select.trigger('change');

        });

        // Chosen order abstract
        $this.SGU_FWChosenOrder($hidden_value, true);

      }

      // Chosen sortable
      if ( is_sortable ) {

        var $chosen_container = $this.parent().find('.chosen-container');
        var $chosen_choices   = $chosen_container.find('.chosen-choices');

        $chosen_choices.bind('mousedown', function( event ) {
          if ( $(event.target).is('span') ) {
            event.stopPropagation();
          }
        });

        $chosen_choices.sortable({
          items: 'li:not(.search-field)',
          helper: 'orginal',
          cursor: 'move',
          placeholder: 'search-choice-placeholder',
          start: function(e,ui) {
            ui.placeholder.width( ui.item.innerWidth() );
            ui.placeholder.height( ui.item.innerHeight() );
          },
          update: function( e, ui ) {

            var select_options = '';
            var chosen_object  = $this.data('chosen');
            var $prev_select   = $this.parent().find('.sgu_fw-hide-select');

            $chosen_choices.find('.search-choice-close').each( function() {
              var option_array_index = $(this).data('option-array-index');
              $.each(chosen_object.results_data, function(index, data) {
                if ( data.array_index === option_array_index ){
                  select_options += '<option value="'+ data.value +'" selected>'+ data.value +'</option>';
                }
              });
            });

            $prev_select.children().remove();
            $prev_select.append(select_options);
            $prev_select.trigger('change');

          }
        });

      }

    });
  };

  //
  // Helper Checkbox Checker
  //
  $.fn.sgu_fw_checkbox = function() {
    return this.each( function() {

      var $this     = $(this),
          $input    = $this.find('.sgu_fw--input'),
          $checkbox = $this.find('.sgu_fw--checkbox');

      $checkbox.on('click', function() {
        $input.val( Number( $checkbox.prop('checked') ) ).trigger('change');
      });

    });
  };

  //
  // Helper Check/Uncheck All
  //
  $.fn.sgu_fw_checkbox_all = function() {
    return this.each( function() {

      var $this = $(this);

      $this.on('click', function() {

        var $inputs = $this.closest('.sgu_fw-field-checkbox').find(':input'),
            uncheck = false;

        $inputs.each(function() {
          if ( ! $(this).prop('checked') ) {
            uncheck = true;
          }
        });

        if ( uncheck ) {
          $inputs.prop('checked', 'checked');
          $inputs.attr('checked', 'checked');
        } else {
          $inputs.prop('checked', '');
          $inputs.removeAttr('checked');
        }

        $inputs.first().trigger('change');

      });

    });
  };

  //
  // Siblings
  //
  $.fn.sgu_fw_siblings = function() {
    return this.each( function() {

      var $this     = $(this),
          $siblings = $this.find('.sgu_fw--sibling'),
          multiple  = $this.data('multiple') || false;

      $siblings.on('click', function() {

        var $sibling = $(this);

        if ( multiple ) {

          if ( $sibling.hasClass('sgu_fw--active') ) {
            $sibling.removeClass('sgu_fw--active');
            $sibling.find('input').prop('checked', false).trigger('change');
          } else {
            $sibling.addClass('sgu_fw--active');
            $sibling.find('input').prop('checked', true).trigger('change');
          }

        } else {

          $this.find('input').prop('checked', false);
          $sibling.find('input').prop('checked', true).trigger('change');
          $sibling.addClass('sgu_fw--active').siblings().removeClass('sgu_fw--active');

        }

      });

    });
  };

  //
  // Help Tooltip
  //
  $.fn.sgu_fw_help = function() {
    return this.each( function() {

      var $this = $(this),
          $tooltip,
          offset_left;

      $this.on({
        mouseenter: function() {

          $tooltip = $( '<div class="sgu_fw-tooltip"></div>' ).html( $this.find('.sgu_fw-help-text').html() ).appendTo('body');
          offset_left = ( SGU_FW.vars.is_rtl ) ? ( $this.offset().left + 24 ) : ( $this.offset().left - $tooltip.outerWidth() );

          $tooltip.css({
            top: $this.offset().top - ( ( $tooltip.outerHeight() / 2 ) - 14 ),
            left: offset_left,
          });

        },
        mouseleave: function() {

          if ( $tooltip !== undefined ) {
            $tooltip.remove();
          }

        }

      });

    });
  };

  //
  // Customize Refresh
  //
  $.fn.sgu_fw_customizer_refresh = function() {
    return this.each( function() {

      var $this    = $(this),
          $complex = $this.closest('.sgu_fw-customize-complex');

      if ( $complex.length ) {

       var unique_id = $complex.data('unique-id');

        if ( unique_id === undefined ) {
          return;
        }

        var $input    = $complex.find(':input'),
            option_id = $complex.data('option-id'),
            obj       = $input.serializeObjectSGU_FW(),
            data      = ( ! $.isEmptyObject(obj) && obj[unique_id] && obj[unique_id][option_id] ) ? obj[unique_id][option_id] : '',
            control   = window.wp.customize.control(unique_id +'['+ option_id +']');

        // clear the value to force refresh.
        control.setting._value = null;

        control.setting.set( data );

      } else {

        $this.find(':input').first().trigger('change');

      }

      $(document).trigger('sgu_fw-customizer-refresh', $this);

    });
  };

  //
  // Customize Listen Form Elements
  //
  $.fn.sgu_fw_customizer_listen = function( options ) {

    var settings = $.extend({
      closest: false,
    }, options );

    return this.each( function() {

      if ( window.wp.customize === undefined ) { return; }

      var $this     = ( settings.closest ) ? $(this).closest('.sgu_fw-customize-complex') : $(this),
          $input    = $this.find(':input'),
          unique_id = $this.data('unique-id'),
          option_id = $this.data('option-id');

      if ( unique_id === undefined ) {
        return;
      }

      $input.on('change keyup sgu_fw.change', function() {

        var obj = $this.find(':input').serializeObjectSGU_FW();
        var val = ( !$.isEmptyObject(obj) && obj[unique_id] && obj[unique_id][option_id] ) ? obj[unique_id][option_id] : '';

        window.wp.customize.control( unique_id +'['+ option_id +']' ).setting.set( val );

      });

    });
  };

  //
  // Customizer Listener for Reload JS
  //
  $(document).on('expanded', '.control-section', function() {

    var $this  = $(this);

    if ( $this.hasClass('open') && !$this.data('inited') ) {

      var $fields  = $this.find('.sgu_fw-customize-field');
      var $complex = $this.find('.sgu_fw-customize-complex');

      if ( $fields.length ) {
        $this.sgu_fw_dependency();
        $fields.sgu_fw_reload_script({dependency: false});
        $complex.sgu_fw_customizer_listen();
      }

      $this.data('inited', true);

    }

  });

  //
  // Window on resize
  //
  SGU_FW.vars.$window.on('resize sgu_fw.resize', SGU_FW.helper.debounce( function( event ) {

    var window_width = navigator.userAgent.indexOf('AppleWebKit/') > -1 ? SGU_FW.vars.$window.width() : window.innerWidth;

    if ( window_width <= 782 && !SGU_FW.vars.onloaded ) {
      $('.sgu_fw-section').sgu_fw_reload_script();
      SGU_FW.vars.onloaded  = true;
    }

  }, 200)).trigger('sgu_fw.resize');

  //
  // Widgets Framework
  //
  $.fn.sgu_fw_widgets = function() {
    return this.each( function() {

      $(document).on('widget-added widget-updated', function( event, $widget ) {

        var $fields = $widget.find('.sgu_fw-fields');

        if ( $fields.length ) {
          $fields.sgu_fw_reload_script();
        }

      });

      $(document).on('click', '.widget-top', function( event ) {

        var $fields = $(this).parent().find('.sgu_fw-fields');

        if ( $fields.length ) {
          $fields.sgu_fw_reload_script();
        }

      });

      $('.widgets-sortables, .control-section-sidebar').on('sortstop', function( event, ui ) {
        ui.item.find('.sgu_fw-fields').sgu_fw_reload_script_retry();
      });

    });
  };

  //
  // Nav Menu Options Framework
  //
  $.fn.sgu_fw_nav_menu = function() {
    return this.each( function() {

      var $navmenu = $(this);

      $navmenu.on('click', 'a.item-edit', function() {
        $(this).closest('li.menu-item').find('.sgu_fw-fields').sgu_fw_reload_script();
      });

      $navmenu.on('sortstop', function( event, ui ) {
        ui.item.find('.sgu_fw-fields').sgu_fw_reload_script_retry();
      });

    });
  };

  //
  // Retry Plugins
  //
  $.fn.sgu_fw_reload_script_retry = function() {
    return this.each( function() {

      var $this = $(this);

      if ( $this.data('inited') ) {
        $this.children('.sgu_fw-field-wp_editor').sgu_fw_field_wp_editor();
      }

    });
  };

  //
  // Reload Plugins
  //
  $.fn.sgu_fw_reload_script = function( options ) {

    var settings = $.extend({
      dependency: true,
    }, options );

    return this.each( function() {

      var $this = $(this);

      // Avoid for conflicts
      if ( !$this.data('inited') ) {

        // Field plugins
        $this.children('.sgu_fw-field-accordion').sgu_fw_field_accordion();
        $this.children('.sgu_fw-field-backup').sgu_fw_field_backup();
        $this.children('.sgu_fw-field-background').sgu_fw_field_background();
        $this.children('.sgu_fw-field-code_editor').sgu_fw_field_code_editor();
        $this.children('.sgu_fw-field-date').sgu_fw_field_date();
        $this.children('.sgu_fw-field-datetime').sgu_fw_field_datetime();
        $this.children('.sgu_fw-field-fieldset').sgu_fw_field_fieldset();
        $this.children('.sgu_fw-field-gallery').sgu_fw_field_gallery();
        $this.children('.sgu_fw-field-group').sgu_fw_field_group();
        $this.children('.sgu_fw-field-icon').sgu_fw_field_icon();
        $this.children('.sgu_fw-field-link').sgu_fw_field_link();
        $this.children('.sgu_fw-field-media').sgu_fw_field_media();
        $this.children('.sgu_fw-field-map').sgu_fw_field_map();
        $this.children('.sgu_fw-field-repeater').sgu_fw_field_repeater();
        $this.children('.sgu_fw-field-slider').sgu_fw_field_slider();
        $this.children('.sgu_fw-field-sortable').sgu_fw_field_sortable();
        $this.children('.sgu_fw-field-sorter').sgu_fw_field_sorter();
        $this.children('.sgu_fw-field-spinner').sgu_fw_field_spinner();
        $this.children('.sgu_fw-field-switcher').sgu_fw_field_switcher();
        $this.children('.sgu_fw-field-tabbed').sgu_fw_field_tabbed();
        $this.children('.sgu_fw-field-typography').sgu_fw_field_typography();
        $this.children('.sgu_fw-field-upload').sgu_fw_field_upload();
        $this.children('.sgu_fw-field-wp_editor').sgu_fw_field_wp_editor();

        // Field colors
        $this.children('.sgu_fw-field-border').find('.sgu_fw-color').sgu_fw_color();
        $this.children('.sgu_fw-field-background').find('.sgu_fw-color').sgu_fw_color();
        $this.children('.sgu_fw-field-color').find('.sgu_fw-color').sgu_fw_color();
        $this.children('.sgu_fw-field-color_group').find('.sgu_fw-color').sgu_fw_color();
        $this.children('.sgu_fw-field-link_color').find('.sgu_fw-color').sgu_fw_color();
        $this.children('.sgu_fw-field-typography').find('.sgu_fw-color').sgu_fw_color();

        // Field chosenjs
        $this.children('.sgu_fw-field-select').find('.sgu_fw-chosen').sgu_fw_chosen();

        // Field Checkbox
        $this.children('.sgu_fw-field-checkbox').find('.sgu_fw-checkbox').sgu_fw_checkbox();
        $this.children('.sgu_fw-field-checkbox').find('.sgu_fw-checkbox-all').sgu_fw_checkbox_all();

        // Field Siblings
        $this.children('.sgu_fw-field-button_set').find('.sgu_fw-siblings').sgu_fw_siblings();
        $this.children('.sgu_fw-field-image_select').find('.sgu_fw-siblings').sgu_fw_siblings();
        $this.children('.sgu_fw-field-palette').find('.sgu_fw-siblings').sgu_fw_siblings();

        // Help Tooptip
        $this.children('.sgu_fw-field').find('.sgu_fw-help').sgu_fw_help();

        if ( settings.dependency ) {
          $this.sgu_fw_dependency();
        }

        $this.data('inited', true);

        $(document).trigger('sgu_fw-reload-script', $this);

      }

    });
  };

  //
  // Document ready and run scripts
  //
  $(document).ready( function() {

    $('.sgu_fw-save').sgu_fw_save();
    $('.sgu_fw-options').sgu_fw_options();
    $('.sgu_fw-sticky-header').sgu_fw_sticky();
    $('.sgu_fw-nav-options').sgu_fw_nav_options();
    $('.sgu_fw-nav-metabox').sgu_fw_nav_metabox();
    $('.sgu_fw-taxonomy').sgu_fw_taxonomy();
    $('.sgu_fw-page-templates').sgu_fw_page_templates();
    $('.sgu_fw-post-formats').sgu_fw_post_formats();
    $('.sgu_fw-shortcode').sgu_fw_shortcode();
    $('.sgu_fw-search').sgu_fw_search();
    $('.sgu_fw-confirm').sgu_fw_confirm();
    $('.sgu_fw-expand-all').sgu_fw_expand_all();
    $('.sgu_fw-onload').sgu_fw_reload_script();
    $('#widgets-editor').sgu_fw_widgets();
    $('#widgets-right').sgu_fw_widgets();
    $('#menu-to-edit').sgu_fw_nav_menu();

  });

})( jQuery, window, document );
