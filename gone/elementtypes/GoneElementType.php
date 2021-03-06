<?php
namespace Craft;

/**
 * Events - Event element type
 */
class GoneElementType extends BaseElementType
{

	public function getName()
	{
		return Craft::t('Gone');
	}

	public function hasContent()
	{
		return true;
	}

	public function hasTitles()
	{
		return false;
	}

	public function getSources($context = null)
	{
		$sources = array(
			'*' => array(
				'label' => Craft::t('All'),
				'defaultSort' => array('dateCreated', 'desc')
			),
			'gone:1' => array(
				'heading' => 'Redirect Type'
			),
			'gone:2' => array(
				'label' => Craft::t('301 - Permanent'),
                'criteria' => array('redirect' => 301),
                'defaultSort' => array('dateCreated', 'desc')
			),
			'gone:3' => array(
				'label' => Craft::t('302 - Temporary'),
                'criteria' => array('redirect' => 302),
                'defaultSort' => array('dateCreated', 'desc')
			),
			'gone:4' => array(
				'label' => Craft::t('410 - Removed'),
                'criteria' => array('redirect' => 410),
                'defaultSort' => array('dateCreated', 'desc')
			),
			'gone:5' => array(
				'heading' => 'Type'
			),
			'gone:6' => array(
				'label' => Craft::t('Entry'),
                'criteria' => array('type' => 'Entry'),
                'defaultSort' => array('dateCreated', 'desc')
			),
			'gone:7' => array(
				'label' => Craft::t('Category'),
                'criteria' => array('type' => 'Category'),
                'defaultSort' => array('dateCreated', 'desc')
			),
			'gone:8' => array(
				'label' => Craft::t('Product'),
                'criteria' => array('type' => 'Product'),
                'defaultSort' => array('dateCreated', 'desc')
			)
		);

		return $sources;
	}
	
    public function getAvailableActions($source = null)
    {
        $actions = array();

        $deleteAction = craft()->elements->getAction('Delete');
        $deleteAction->setParams(
        	array(
            	'confirmationMessage' => Craft::t('Are you sure you want to delete the selected elements?'),
				'successMessage' => Craft::t('Elements deleted.'),
			)
		);
        
        $actions[] = $deleteAction;

        return $actions;
    }

	public function defineTableAttributes($source = null)
	{
		return array(
			'id' => Craft::t('ID'),
			'uri' => Craft::t('URI'),
			'elementId' => Craft::t('Redirect To'),
			'redirect' => Craft::t('Redirect'),
			'type' => Craft::t('Type'),
			'dateCreated' =>  Craft::t('Date Created')
		);
	}

	public function getTableAttributeHtml(BaseElementModel $element, $attribute)
	{

        switch ($attribute) {
	        case 'uri': 
	        
	        	return '<a href="' . UrlHelper::getCpUrl('gone/edit/' . $element->id) . '">' . $element->uri . "</a>";
	        	break;
	        
            case 'elementId':
            
                $redirectElement = craft()->db->createCommand()
                        ->select('uri')
                        ->from('elements_i18n')
                        ->where('elementId=:elementId', array(':elementId' => $element->elementId))
                        ->queryScalar();

                return $redirectElement;
                break;
                
			case 'redirect':
			
				return '<span class="gone-redirect-type gone-redirect-type-' . $element->redirect . '">' . $element->redirect . '</span>';
				break;

            default:
                return parent::getTableAttributeHtml($element, $attribute);
                break;
        }

	}
	
    public function defineSortableAttributes()
    {
        return array(
            'id'    => Craft::t('ID'),
            'uri' => Craft::t('URI'),
            'dateCreated' => Craft::t('Date Created')
        );
    }

	public function defineCriteriaAttributes()
	{
		return array(
			'order'      => array(AttributeType::String, 'default' => 'dateCreated desc'),
			'uri' => AttributeType::Mixed,
			'type' => AttributeType::Mixed,
			'title' => AttributeType::Mixed,
			'redirect' => AttributeType::Mixed
		);
	}

	public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
	{
		
		$query
			->addSelect('gone.elementId, gone.type, gone.title, gone.slug, gone.uri, gone.redirect, gone.dateCreated')
			->join('gone gone', 'gone.id = elements.id');
			
		if ($criteria->redirect)
		{
			$query->andWhere(DbHelper::parseParam('gone.redirect', $criteria->redirect, $query->params));
		}
		
		if ($criteria->type)
		{
			$query->andWhere(DbHelper::parseParam('gone.type', $criteria->type, $query->params));
		}
		
        if ($criteria->order) {
            // Pinched from Amforms after endless sort errors! (Thanks!)
            if (stripos($criteria->order, 'gone.dateCreated') === false) {
                $criteria->order = str_replace('dateCreated', 'gone.dateCreated', $criteria->order);
            }
        }

	}

	public function populateElementModel($row)
	{
		return GoneModel::populateModel($row);
	}
	
}
