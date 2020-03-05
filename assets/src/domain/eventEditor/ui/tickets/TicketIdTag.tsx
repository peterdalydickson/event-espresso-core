import React from 'react';

import useTicketItem from '@edtrServices/apollo/queries/tickets/useTicketItem';
import { ListItemProps } from '@edtrInterfaces/types';

const TicketIdTag: React.FC<ListItemProps> = ({ id }) => {
	const { dbId } = useTicketItem({ id }) || {};
	return dbId ? <code>{dbId}</code> : null;
};

export default TicketIdTag;