export enum SortTickets {
	chronologically = 'chronologically',
	byName = 'byName',
	byId = 'byId',
	byOrder = 'byOrder',
}

export enum DisplayDates {
	start = 'start',
	end = 'end',
	both = 'both',
}

export enum ShowTickets {
	above50Sold = 'above-50-sold',
	above75Sold = 'above-75-sold',
	above90Sold = 'above-90-sold',
	all = 'all',
	archivedOnly = 'archived-only',
	below50Sold = 'below-50-sold',
	expiredOnly = 'expired-only',
	nextOnSaleOrPendingOnly = 'next-on-sale-or-pending-only',
	onSaleAndPending = 'on-sale-and-pending',
	onSaleOnly = 'on-sale-only',
	pendingOnly = 'pending-only',
	soldOutOnly = 'sold-out-only',
}
