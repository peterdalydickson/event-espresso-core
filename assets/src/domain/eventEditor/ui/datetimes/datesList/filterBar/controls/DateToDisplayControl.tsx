import React from 'react';
import { __ } from '@wordpress/i18n';
import { SelectInput } from '@appInputs/SelectInput';
import { DateToDisplay } from '@sharedServices/filterState';

interface DateToDisplayControlProps {
	dateToDisplay: DateToDisplay;
	setDateToDisplay: (dateToDisplay: DateToDisplay) => void;
}
/**
 * filter for controlling which dates display in a list of Event Dates
 */
const DateToDisplayControl: React.FC<DateToDisplayControlProps> = React.memo(({ dateToDisplay, setDateToDisplay }) => {
	return (
		<SelectInput
			label={__('display')}
			className='espresso-date-list-filter-bar-display-select'
			value={dateToDisplay}
			options={[
				{
					value: DateToDisplay.start,
					label: __('start dates only'),
				},
				{
					value: DateToDisplay.end,
					label: __('end dates only'),
				},
				{
					value: DateToDisplay.both,
					label: __('start and end dates'),
				},
			]}
			onChange={setDateToDisplay}
			size='large'
		/>
	);
});

export default DateToDisplayControl;
