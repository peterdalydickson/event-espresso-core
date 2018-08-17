<?php

namespace EventEspresso\core\domain\entities\route_match\specifications\admin;

use EventEspresso\core\domain\entities\route_match\RouteMatchSpecification;

/**
 * Class EspressoVenueEditorEdit
 * Returns true when the current request is for the Venue Editor admin page for an existing venue
 *
 * @package EventEspresso\core\domain\entities\route_match\specifications\admin
 * @author  Brent Christensen
 * @since   $VID:$
 */
class EspressoVenueEditorEdit extends RouteMatchSpecification
{
    /**
     * returns true if current request matches specification
     *
     * @since $VID:$
     * @return boolean
     */
    public function isMatchingRoute()
    {
        return $this->request->getRequestParam('page') === 'espresso_venues'
            && $this->request->getRequestParam('action') === 'edit';
    }
}
