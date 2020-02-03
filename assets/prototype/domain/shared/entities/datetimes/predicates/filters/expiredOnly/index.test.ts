/**
 * Internal dependencies
 */
import expiredOnly from './index';
import { nodes as datetimes } from '../../../../../../eventEditor/data/queries/datetimes/test/data';

const datetime = datetimes[0];

describe('expiredOnly', () => {
	it('Should return empty array if dates are not expired', () => {
		const filteredDates = expiredOnly([
			{ ...datetime, id: 'abc', isExpired: false, isTrashed: true },
			{ ...datetime, id: 'def', isExpired: false, isTrashed: true },
		]);

		expect(filteredDates).toEqual([]);
	});

	it('Should return an array of dates that are expired', () => {
		const filteredDates = expiredOnly([
			{ ...datetime, id: 'abc', isExpired: false },
			{ ...datetime, id: 'def', isExpired: false },
			{ ...datetime, id: 'xyz', isExpired: true },
		]);

		expect(filteredDates.length).toBe(1);
		expect(filteredDates[0].id).toBe('xyz');
	});

	it('Should return an array of dates that are expired and not trashed', () => {
		const filteredDates = expiredOnly([
			{ ...datetime, id: 'abc', isExpired: false, isTrashed: true },
			{ ...datetime, id: 'def', isExpired: true, isTrashed: false },
			{ ...datetime, id: 'xyz', isExpired: true, isTrashed: true },
		]);

		expect(filteredDates.length).toBe(1);
		expect(filteredDates[0].id).toBe('def');
	});
});
