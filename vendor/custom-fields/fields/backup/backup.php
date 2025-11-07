<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
/**
 *
 * Field: backup
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'SGU_FW_Field_backup' ) ) {
  class SGU_FW_Field_backup extends SGU_FW_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $unique = $this->unique;
      $nonce  = wp_create_nonce( 'sgu_fw_backup_nonce' );
      $export = add_query_arg( array( 'action' => 'sgu_fw-export', 'unique' => $unique, 'nonce' => $nonce ), admin_url( 'admin-ajax.php' ) );

      echo $this->field_before();

      echo '<textarea name="sgu_fw_import_data" class="sgu_fw-import-data"></textarea>';
      echo '<button type="submit" class="button button-primary sgu_fw-confirm sgu_fw-import" data-unique="'. esc_attr( $unique ) .'" data-nonce="'. esc_attr( $nonce ) .'">'. esc_html__( 'Import', 'sgu_fw' ) .'</button>';
      echo '<hr />';
      echo '<textarea readonly="readonly" class="sgu_fw-export-data">'. esc_attr( json_encode( get_option( $unique ) ) ) .'</textarea>';
      echo '<a href="'. esc_url( $export ) .'" class="button button-primary sgu_fw-export" target="_blank">'. esc_html__( 'Export & Download', 'sgu_fw' ) .'</a>';
      echo '<hr />';
      echo '<button type="submit" name="sgu_fw_transient[reset]" value="reset" class="button sgu_fw-warning-primary sgu_fw-confirm sgu_fw-reset" data-unique="'. esc_attr( $unique ) .'" data-nonce="'. esc_attr( $nonce ) .'">'. esc_html__( 'Reset', 'sgu_fw' ) .'</button>';

      echo $this->field_after();

    }

  }
}
