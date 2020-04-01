import { pick, map, mapObjIndexed } from 'ramda';

import { useRelationsManager, RelationFunctionProps } from '@appServices/apollo/relations';
import { AssignmentManager, TAMRelationalData } from '../types';

type AM = AssignmentManager;
/**
 * A wrapper for relations manager.
 */
const useAssignmentManager = (): AM => {
	// Create a fresh instance to manage current relations/assignments
	// without modifying/mutating the existing relations
	const {
		addRelation,
		getData,
		getRelations,
		initialize: initializeRelations,
		isInitialized,
		removeRelation,
	} = useRelationsManager();

	const getAssignedTickets: AM['getAssignedTickets'] = ({ datetimeId }) => {
		return getRelations({
			entity: 'datetimes',
			entityId: datetimeId,
			relation: 'tickets',
		});
	};

	const getAssignedDates: AM['getAssignedDates'] = ({ ticketId }) => {
		return getRelations({
			entity: 'tickets',
			entityId: ticketId,
			relation: 'datetimes',
		});
	};

	const addAssignment: AM['addAssignment'] = ({ datetimeId, ticketId }) => {
		updateAssignment({ datetimeId, ticketId });
	};

	// args are same
	const toggleAssignment: AM['addAssignment'] = ({ datetimeId, ticketId }) => {
		const assignedTickets = getAssignedTickets({ datetimeId });
		const remove = assignedTickets.includes(ticketId);
		updateAssignment({ datetimeId, ticketId, remove });
	};

	const removeAssignment: AM['removeAssignment'] = ({ datetimeId, ticketId }) => {
		updateAssignment({ datetimeId, ticketId, remove: true });
	};

	// args are same
	const updateAssignment: AM['removeAssignment'] = ({ datetimeId, ticketId, remove = false }) => {
		// relation from datetimes towards tickets
		const datetimeToTickets: RelationFunctionProps<'datetimes'> = {
			entity: 'datetimes',
			entityId: datetimeId,
			relation: 'tickets',
			relationId: ticketId,
		};
		// relation from tickets towards datetimes
		const ticketsToDatetimes: RelationFunctionProps<'tickets'> = {
			entity: 'tickets',
			entityId: ticketId,
			relation: 'datetimes',
			relationId: datetimeId,
		};

		if (remove) {
			removeRelation(datetimeToTickets);
			removeRelation(ticketsToDatetimes);
		} else {
			// Add both ways relation for fast retieval
			addRelation(datetimeToTickets);
			addRelation(ticketsToDatetimes);
		}
	};

	const initialize: AM['initialize'] = ({ data, assignmentType, entityId }) => {
		const relationsToPick: Array<keyof TAMRelationalData> = ['datetimes', 'tickets'];
		// pick only datetimes and tickets from relational data
		let newData = pick(relationsToPick, data);

		// Remove other relations from newData
		// like ticket to price relations
		newData = mapObjIndexed((relationalEntity, entity) => {
			let relationalEntityToUse = relationalEntity;
			// If TAM is only for a single datetime/ticket
			// limit relations to that datetime/ticket
			if (
				(assignmentType === 'forDate' && entity === 'datetimes') ||
				(assignmentType === 'forTicket' && entity === 'tickets')
			) {
				relationalEntityToUse = pick([entityId], relationalEntity);
			}

			return map((relation) => {
				return pick(relationsToPick, relation);
			}, relationalEntityToUse);
		}, newData);

		initializeRelations(newData);
	};

	return {
		addAssignment,
		getAssignedDates,
		getAssignedTickets,
		getData,
		initialize,
		isInitialized,
		removeAssignment,
		toggleAssignment,
	};
};

export default useAssignmentManager;