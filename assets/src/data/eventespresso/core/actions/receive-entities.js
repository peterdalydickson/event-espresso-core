/**
 * Internal imports
 */
import { ACTION_TYPES } from './action-types';
const { entities: types } = ACTION_TYPES;

/**
 * External imports
 */
import { singularModelName } from '@eventespresso/model';

/**
 * Action for receiving entity records.
 * Entity records received will NOT replace any existing entities in the state
 * (matching ids).
 *
 * @param {string} modelName expected to be an model name.
 * @param {Array<BaseEntity>} entities Array of model entity
 * instances indexed by the primary key.
 * @return {
 * {
 * 	type: string,
 * 	modelName: string,
 * 	entities: Array<BaseEntity>
 * 	}
 * }  An action object.
 */
function receiveEntityRecords( modelName, entities = [] ) {
	modelName = singularModelName( modelName );
	return {
		type: types.RECEIVE_ENTITY_RECORDS,
		modelName,
		entities,
	};
}

/**
 * Same as receiveEntityRecords except incoming entities will replace any
 * matching records (by ID)  in the state.
 * @param {string} modelName
 * @param {Array} entities
 * @return {
 * {
 * 	type: string,
 * 	modelName: string,
 * 	entities: Array<BaseEntity>
 * 	}
 * }  An action object.
 */
function receiveAndReplaceEntityRecords( modelName, entities = [] ) {
	modelName = singularModelName( modelName );
	return {
		type: types.RECEIVE_AND_REPLACE_ENTITY_RECORDS,
		modelName,
		entities,
	};
}

/**
 * Action creator for adding an entity to the state (as opposed to an entity
 * record)
 *
 * @param {BaseEntity} entity
 * @return {{type: string, entity: BaseEntity}} An action
 * object.
 */
function receiveEntity( entity ) {
	return {
		type: types.RECEIVE_ENTITY,
		entity,
	};
}

/**
 * Action creator for queuing an entity id for trash.
 *
 * @param {string} modelName
 * @param {number} entityId
 * @return {{type: string, modelName: string, entityId: number}} An action
 * object.
 */
function receiveTrashEntityId( modelName, entityId ) {
	modelName = singularModelName( modelName );
	return {
		type: types.RECEIVE_TRASH_ENTITY_ID,
		modelName,
		entityId,
	};
}

/**
 * Action creator for queueing an entity id for hard delete.
 *
 * @param {string} modelName
 * @param {number} entityId
 * @return {{type: string, modelName: string, entityId: number}} An action
 * object.
 */
function receiveDeleteEntityId( modelName, entityId ) {
	modelName = singularModelName( modelName );
	return {
		type: types.RECEIVE_DELETE_ENTITY_ID,
		modelName,
		entityId,
	};
}

export {
	receiveEntityRecords,
	receiveAndReplaceEntityRecords,
	receiveEntity,
	receiveDeleteEntityId,
	receiveTrashEntityId,
};
