import { useCallback } from 'react';
import { OperationVariables } from 'apollo-client';

import { MutationType, MutationInput } from '@appServices/apollo/mutations';

type MutationVariablesCb = (mutationType: MutationType, input: MutationInput) => OperationVariables;

const useMutationVariables = (): MutationVariablesCb => {
	return useCallback<MutationVariablesCb>((mutationType, input) => {
		const mutationInput: MutationInput = {
			clientMutationId: `${mutationType}_TICKET`,
			...input,
		};

		return {
			input: mutationInput,
		};
	}, []);
};

export default useMutationVariables;
