<?php
/**
 * Gone
 *
 * @author    Jason Mayo
 * @twitter   @madebymayo
 * @package   Gone
 *
 */

namespace Craft;

class GonePlugin extends BasePlugin
{

    public function getName()
    {
         return Craft::t('Gone');
    }

    public function getDescription()
    {
        return Craft::t('Elements that have been deleted will return a 410 instead of a 404 error');
    }

    public function getDocumentationUrl()
    {
        return 'https://github.com/madebyshape/gone/blob/master/README.md';
    }

    public function getReleaseFeedUrl()
    {
        return 'https://raw.githubusercontent.com/madebyshape/gone/master/releases.json';
    }

    public function getVersion()
    {
        return '1.0.2';
    }

    public function getSchemaVersion()
    {
        return '1.0.2';
    }

    public function getDeveloper()
    {
        return 'Jason Mayo';
    }

    public function getDeveloperUrl()
    {
        return 'bymayo.co.uk';
    }

    public function hasCpSection()
    {
        return true;
    }
    
    public function init()
    {
	    
	    if(!craft()->isConsole() && craft()->request->isSiteRequest()) {
    
	    	$uri = trim(craft()->request->getUrl(), '/');
	    	$gone = craft()->gone->checkUri($uri);
	    	
	    	if ($gone) {
		    	throw new HttpException('410');
	    	}
    	
    	}

		craft()->on(
			'entries.deleteEntry', 
			function(Event $event) {
				$element = $event->params['entry'];
				if ($element->elementType === 'Entry') {
					// Temporarily check if element is an entry
					craft()->gone->add($element);
				}
			}
		);
		
		craft()->on(
			'elements.onBeforePerformAction',
			function(Event $event) {
				
				// Get 'Action' type
				$action = $event->params['action']->classHandle;
				
				// Get Elements within the action
				$elements = $event->params['criteria']->find();
				
				if ($action == 'Delete') {
					foreach ($elements as $element) {
						if ($element->elementType === 'Entry') {
							// Temporarily check if element is an entry
							craft()->gone->add($element);	
						}
					}
				}
				
			}
		);
			
		// Categories
		// Users
		// Locales
		// Assets
		// Commerce
	    
    }
    
}