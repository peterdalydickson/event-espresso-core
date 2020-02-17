import { useMutator as useAppMutator, MutatorObject } from '../../../../../application/services/apollo/mutations';
import { TypeName } from './types';
import { Entity } from '../types';
import useMutationOptions from './useMutationOptions';

/**
 * @param {string} typeName Entity type name
 * @param {string} id       Entity id
 */
const useMutator = <Type extends Entity>(typeName: TypeName, id = ''): MutatorObject => {
	const mutationOptionsCallback = useMutationOptions<TypeName>(typeName);

	const mutator = useAppMutator(mutationOptionsCallback);
	return mutator<TypeName, Type>(typeName, id);
};

export default useMutator;
