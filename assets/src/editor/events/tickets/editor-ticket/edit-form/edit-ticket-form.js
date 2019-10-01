/**
 * External imports
 */
import { isEmpty } from 'lodash';
import { useMemo } from '@wordpress/element';
import { twoColumnAdminFormLayout } from '@eventespresso/components';
import { ifValidTicketEntity } from '@eventespresso/editor-hocs';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import useTicketFormInputConfig from './use-ticket-form-input-config';
import useTicketFormInputPrefix
	from './use-ticket-form-input-prefix';
import useEditEntityFormInputs
	from '../../../../helpers/forms/use-edit-entity-form-inputs';

const {
	FormSection,
	FormWrapper,
} = twoColumnAdminFormLayout;

const EditTicketForm = ( {
	ticketEntity,
	currentValues,
	initialValues,
	newObject,
} ) => {
	const formDataKeyPrefix = useTicketFormInputPrefix( ticketEntity );
	const inputConfig = useTicketFormInputConfig( ticketEntity );
	// entity properties we don't want to be editable
	const exclude = [
		'TKT_ID',
		'sold',
		'reserved',
		'order',
		'parent',
		'wpUser',
		'status',
	];
	const formRows = useEditEntityFormInputs(
		ticketEntity,
		inputConfig,
		formDataKeyPrefix,
		currentValues,
		exclude
	);
	return useMemo(
		() => {
			// edit forms for existing objects must have initial values
			return ! ( ! newObject && isEmpty( initialValues ) ) ?
				(
					<FormWrapper>
						<FormSection
							htmlId={ `ee-ticket-editor-${ ticketEntity.id }-form-section` }
							children={ formRows }
							showRequiredNotice={ true }
						/>
					</FormWrapper>
				) : null;
		},
		[
			newObject,
			initialValues,
			ticketEntity,
			formRows,
		]
	);
};

EditTicketForm.propTypes = {
	ticketEntity: PropTypes.object.isRequired,
	currentValues: PropTypes.object,
	initialValues: PropTypes.object,
	newObject: PropTypes.bool,
};

EditTicketForm.defaultProps = {
	currentValues: {},
	initialValues: {},
	newObject: false,
};

/**
 * Enhanced EditTicketForm with FormHandler
 */
export default ifValidTicketEntity( EditTicketForm );