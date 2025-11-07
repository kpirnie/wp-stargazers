<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
/**
 *
 * Field: icon
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'SGU_FW_Field_icon' ) ) {
  class SGU_FW_Field_icon extends SGU_FW_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $args = wp_parse_args( $this->field, array(
        'button_title' => esc_html__( 'Add Icon', 'sgu_fw' ),
        'remove_title' => esc_html__( 'Remove Icon', 'sgu_fw' ),
      ) );

      echo $this->field_before();

      $nonce  = wp_create_nonce( 'sgu_fw_icon_nonce' );
      $hidden = ( empty( $this->value ) ) ? ' hidden' : '';

      echo '<div class="sgu_fw-icon-select">';
      echo '<span class="sgu_fw-icon-preview'. esc_attr( $hidden ) .'"><i class="'. esc_attr( $this->value ) .'"></i></span>';
      echo '<a href="#" class="button button-primary sgu_fw-icon-add" data-nonce="'. esc_attr( $nonce ) .'">'. $args['button_title'] .'</a>';
      echo '<a href="#" class="button sgu_fw-warning-primary sgu_fw-icon-remove'. esc_attr( $hidden ) .'">'. $args['remove_title'] .'</a>';
      echo '<input type="hidden" name="'. esc_attr( $this->field_name() ) .'" value="'. esc_attr( $this->value ) .'" class="sgu_fw-icon-value"'. $this->field_attributes() .' />';
      echo '</div>';

      echo $this->field_after();

    }

    public function enqueue() {
      add_action( 'admin_footer', array( 'SGU_FW_Field_icon', 'add_footer_modal_icon' ) );
      add_action( 'customize_controls_print_footer_scripts', array( 'SGU_FW_Field_icon', 'add_footer_modal_icon' ) );
    }

    public static function add_footer_modal_icon() {
    ?>
      <div id="sgu_fw-modal-icon" class="sgu_fw-modal sgu_fw-modal-icon hidden">
        <div class="sgu_fw-modal-table">
          <div class="sgu_fw-modal-table-cell">
            <div class="sgu_fw-modal-overlay"></div>
            <div class="sgu_fw-modal-inner">
              <div class="sgu_fw-modal-title">
                <?php esc_html_e( 'Add Icon', 'sgu_fw' ); ?>
                <div class="sgu_fw-modal-close sgu_fw-icon-close"></div>
              </div>
              <div class="sgu_fw-modal-header">
                <input type="text" placeholder="<?php esc_html_e( 'Search...', 'sgu_fw' ); ?>" class="sgu_fw-icon-search" />
              </div>
              <div class="sgu_fw-modal-content">
                <div class="sgu_fw-modal-loading"><div class="sgu_fw-loading"></div></div>
                <div class="sgu_fw-modal-load"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php
    }

  }
}
