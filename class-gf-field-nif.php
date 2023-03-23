<?php
if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class GF_Field_NIF extends GF_Field {

	public $type = 'nif';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GF_Field_NIF
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GF_Field_NIF();
			self::$_instance->add_hooks();
		}

		return self::$_instance;
	}

	public function __construct( $data = array() ) {
		parent::__construct( $data );
		if ( empty( $data ) ) {
			return;
		}
		foreach ( $data as $key => $value ) {
			$this->{$key} = $value;
		}
	}

	/**
	 * Handles hooks and loading of language files.
	 */
	public function add_hooks() {
		add_action( 'gform_field_standard_settings', array( $this, 'nif_setting' ), 10, 2 );
		//Action to inject supporting script to the form editor page
		add_action( 'gform_editor_js', array( $this, 'editor_script' ) );
		//Filter to add a new tooltip
		add_filter( 'gform_tooltips', array( $this, 'add_nif_setting_tooltips' ) );
	}

	function nif_setting( $position, $form_id ) {
		if ( $position == 25 ) {
			?>
			<li class="nif_setting field_setting">
				<label for="nif_setting" class="section_label"><?php _e("Valid documents", "gravityforms-nif"); ?></label>
				<ul class="nif_setting_container">
					<li>
						<input type="checkbox" id="field_nif_dni" onclick="SetFieldProperty('nifFieldAcceptDNI', this.checked);" />
						<label for="field_nif_dni" style="display:inline;">
							<?php _e("Accept DNIs", "gravityforms-nif"); ?>
							<?php gform_tooltip("form_field_nif_dni") ?>
						</label>
					</li>
					<li>
						<input type="checkbox" id="field_nif_nie" onclick="SetFieldProperty('nifFieldAcceptNIE', this.checked);" />
						<label for="field_nif_nie" style="display:inline;">
							<?php _e("Accept NIEs", "gravityforms-nif"); ?>
							<?php gform_tooltip("form_field_nif_nie") ?>
						</label>
					</li>
					<li>
						<input type="checkbox" id="field_nif_cif" onclick="SetFieldProperty('nifFieldAcceptCIF', this.checked);" />
						<label for="field_nif_cif" style="display:inline;">
							<?php _e("Accept CIFs", "gravityforms-nif"); ?>
							<?php gform_tooltip("form_field_nif_cif") ?>
						</label>
					</li>
				</ul>
			</li>
			<?php
		}
	}

	function editor_script(){
		?>
		<script type='text/javascript'>
			//adding setting to fields of type "text"
			fieldSettings.text += ', .nif_setting';
			//binding to the load field settings event to initialize the checkbox
			jQuery(document).on('gform_load_field_settings', function(event, field, form){
				jQuery( '#field_nif_dni' ).prop( 'checked', Boolean( rgar( field, 'nifFieldAcceptDNI' ) ) );
				jQuery( '#field_nif_nie' ).prop( 'checked', Boolean( rgar( field, 'nifFieldAcceptNIE' ) ) );
				jQuery( '#field_nif_cif' ).prop( 'checked', Boolean( rgar( field, 'nifFieldAcceptCIF' ) ) );
			});
			gform.addFilter( 'gform_pre_form_editor_save', function( form ) {
				form.fields = form.fields.map(
					(f,i) => {
						if(
							f.type=='nif' &&
							( f.nifFieldAcceptCIF || f.nifFieldAcceptDNI || f.nifFieldAcceptNIE )
						) {
							f.inputMask = true;
							f.inputMaskIsCustom = true;
							if(f.nifFieldAcceptNIE || (f.nifFieldAcceptDNI && f.nifFieldAcceptCIF)){
								f.inputMaskValue = 'a9999999*?a';
								if(f.nifFieldAcceptDNI){
									f.inputMaskValue = '*9999999*?a';
								}
							} else if(f.nifFieldAcceptDNI){
								f.inputMaskValue = '99999999a';
							} else {
								f.inputMaskValue = 'a99999999';
							}
						}
						return f;
					}
				);
				return form;
			}, 10, 2 );
		</script>
		<?php
	}

	function add_nif_setting_tooltips( $tooltips ) {
		$tooltips['form_field_nif_dni'] = "<h6>NIF</h6>Marcar para aceptar DNIs";
		$tooltips['form_field_nif_nie'] = "<h6>NIE</h6>Marcar para aceptar NIEs";
		$tooltips['form_field_nif_cif'] = "<h6>CIF</h6>Marcar para aceptar CIF/NIFs";
		return $tooltips;
	}

	/**
	 * Returns the HTML tag for the field container.
	 *
	 * @since 2.5
	 *
	 * @param array $form The current Form object.
	 *
	 * @return string
	 */
	public function get_field_container_tag( $form ) {

		if ( GFCommon::is_legacy_markup_enabled( $form ) ) {
			return parent::get_field_container_tag( $form );
		}

		return 'fieldset';

	}

	/**
	 * Returns the field title.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @return string The field title. Escaped.
	 */
	public function get_form_editor_field_title() {
		return esc_attr__( 'NIF', 'nifaddon' );
	}

	/**
	 * Returns the field's form editor description.
	 *
	 * @since 2.5
	 *
	 * @return string
	 */
	public function get_form_editor_field_description() {
		return esc_attr__( 'Allows users to submit DNI, NIE or CIF.', 'nifaddon' );
	}

	/**
	 * Returns the field's form editor icon.
	 *
	 * This could be an icon url or a gform-icon class.
	 *
	 * @since 2.5
	 *
	 * @return string
	 */
	public function get_form_editor_field_icon() {
		return 'gform-icon--credit-card';
	}

	/**
	 * Returns the field button properties for the form editor. The array contains two elements:
	 * 'group' => 'standard_fields' // or  'advanced_fields', 'post_fields', 'pricing_fields'
	 * 'text'  => 'Button text'
	 *
	 * Built-in fields don't need to implement this because the buttons are added in sequence in GFFormDetail
	 *
	 * @return array
	 */
	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
			'icon'  => $this->get_form_editor_field_icon(),
			'description' => $this->get_form_editor_field_description()
		);
	}

	function get_form_editor_field_settings() {
		return array(
			'nif_setting',
			'conditional_logic_field_setting',
			'prepopulate_field_setting',
			'error_message_setting',
			'label_setting',
			'label_placement_setting',
			'admin_label_setting',
			'size_setting',
			'rules_setting',
			'visibility_setting',
			'duplicate_setting',
			'description_setting',
			'css_class_setting'		);
	}

	public function is_conditional_logic_supported() {
		return true;
	}
	/**
	 * Validates Name field inputs.
	 *
	 * @since  1.9
	 * @since  2.6.5 Updated to use set_required_error().
	 * @access public
	 *
	 * @used-by GFFormDisplay::validate()
	 * @uses    GF_Field_Name::$isRequired
	 * @uses    GF_Field_Name::$nameFormat
	 * @uses    GF_Field_Name::get_input_property
	 * @uses    GF_Field_Name::$failed_validation
	 * @uses    GF_Field_Name::$validation_message
	 * @uses    GF_Field_Name::$errorMessage
	 *
	 * @param array|string $value The value of the field to validate. Not used here.
	 * @param array        $form  The Form Object. Not used here.
	 *
	 * @return void
	 */
	function validate( $value, $form ) {
		if ( $this->isRequired  ) {
			$this->set_required_error( $value, true );
		}
		$this->set_invalid_error( $value );
	}
	/**
	 * Sets the failed_validation and validation_message properties for a required field error.
	 *
	 * @since 2.6.5
	 *
	 * @param mixed $value                   The field value.
	 * @param bool  $require_complex_message Indicates if the field must have a complex validation message for the error to be set.
	 *
	 * @return void
	 */
	public function set_invalid_error( $value ) {
		require_once('class-nif-validator.php');
		$validator = new NIF_Validator($value, $this->nifFieldAcceptDNI, $this->nifFieldAcceptNIE, $this->nifFieldAcceptCIF);
		if ( $validator->isValid() ) {
			return;
		}
		$this->failed_validation = true;
		if( $this->nifFieldAcceptDNI + $this->nifFieldAcceptNIE + $this->nifFieldAcceptCIF >1)
			$this->validation_message .= __("Identification document must be either:", "gravityforms-nif");
		else{
			$this->validation_message .= __("Identification document must be:", "gravityforms-nif");
		}
		if($this->nifFieldAcceptDNI){
			$this->validation_message .= "<br/> * ".__("Valid DNI", "gravityforms-nif");
		}
		if($this->nifFieldAcceptNIE){
			$this->validation_message .= "<br/> * ".__("Valid NIE", "gravityforms-nif");
		}
		if($this->nifFieldAcceptCIF){
			$this->validation_message .= "<br/> * ".__("Valid CIF", "gravityforms-nif");
		}
	}

	/**
	 * Returns the field inner markup.
	 *
	 * @param array        $form  The Form Object currently being processed.
	 * @param string|array $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param null|array   $entry Null or the Entry Object currently being edited.
	 *
	 * @return string
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {
		$form_id         = absint( $form['id'] );
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		$id       = (int) $this->id;
		$field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

		$value        = esc_attr( $value );
		$size         = $this->size;
		$class_suffix = $is_entry_detail ? '_admin' : '';
		$class        = $size . $class_suffix;
		$class        = esc_attr( $class );

		$disabled_text = $is_form_editor ? 'disabled="disabled"' : '';

		$tabindex              = $this->get_tabindex();
		$placeholder_attribute = $this->get_field_placeholder_attribute();
		$required_attribute    = $this->isRequired ? 'aria-required="true"' : '';
		$invalid_attribute     = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
		$aria_describedby      = $this->get_aria_describedby();
		return "<div class='ginput_container ginput_container_nif'>
					<input name='input_{$id}' id='{$field_id}' type='text' value='{$value}' class='{$class}' {$tabindex} {$placeholder_attribute} {$disabled_text} {$required_attribute} {$invalid_attribute} {$aria_describedby} />
				</div>";
	}

	public function allow_html() {
		return false;
	}
}
