/**
 * External imports
 */
import { Fragment, useMemo } from '@wordpress/element';
import { twoColumnAdminFormLayout } from '@eventespresso/components';
import { __ } from '@eventespresso/i18n';
const { FormInput, InputLabel } = twoColumnAdminFormLayout;
import PropTypes from 'prop-types';

const PriceDescriptionInput = ( {
	prefix,
	values,
	priceEntity,
} ) => useMemo( () => (
	<Fragment>
		<InputLabel
			label={ __( 'Description', 'event_espresso' ) }
			htmlFor={ `${ prefix }-desc` }
			htmlClass="ee-hidden-label"
		/>
		<FormInput
			key="desc"
			type="textarea"
			name={ `${ prefix }-desc` }
			htmlId={ `${ prefix }-desc` }
			value={ values[ `${ prefix }-desc` ] || '' }
			changeListener={ ( value ) => priceEntity.desc = value }
		/>
	</Fragment>
), [ prefix, values[ `${ prefix }-desc` ], priceEntity.desc ] );

PriceDescriptionInput.propTypes = {
	prefix: PropTypes.string.isRequired,
	values: PropTypes.object.isRequired,
	priceEntity: PropTypes.object.isRequired,
};

export default PriceDescriptionInput;