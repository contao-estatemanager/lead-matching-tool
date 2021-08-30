<?php
declare(strict_types=1);

namespace ContaoEstateManager\LeadMatchingTool\Contao\Dca;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use ContaoEstateManager\LeadMatchingTool\Controller\FrontendModule\LeadMatchingController;
use ContaoEstateManager\ObjectTypeEntity\ObjectTypeModel;
use ContaoEstateManager\RegionEntity\RegionModel;

class TlLeadMatching
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var Database
     */
    private $database;

    /**
     * TlLeadMatching constructor.
     */
    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;

        /** @var Database $dbAdapter */
        $dbAdapter = $this->framework->getAdapter(Database::class);

        $this->database = $dbAdapter->getInstance();
    }

    /**
     * Save key value set of marketing types
     *
     * @param mixed         $varValue
     * @param DataContainer $dc
     *
     * @return string
     */
    public function saveMarketingTypes($varValue, DataContainer $dc): ?string
    {
        if(!$varValue || $dc->activeRecord->type !== LeadMatchingController::TYPE)
        {
            return $varValue;
        }

        $arrChoosedTypes = StringUtil::deserialize($varValue);

        if($arrChoosedTypes === null)
        {
            return $varValue;
        }

        $arrOptions = array();

        foreach ($arrChoosedTypes as $type)
        {
            $arrOptions[ $type ] = &$GLOBALS['TL_LANG']['tl_lead_matching_meta'][ $type ];
        }

        $this->database->prepare("UPDATE tl_lead_matching SET marketingTypesData=? WHERE id=?")
                       ->execute(serialize($arrOptions), $dc->id);

        return $varValue;
    }

    /**
     * Save key value set of object types
     *
     * @param mixed         $varValue
     * @param DataContainer $dc
     *
     * @return string
     */
    public function saveObjectTypes($varValue, DataContainer $dc): ?string
    {
        if(!$varValue || $dc->activeRecord->type !== LeadMatchingController::TYPE)
        {
            return $varValue;
        }

        $arrChoosedTypes = StringUtil::deserialize($varValue);

        if($arrChoosedTypes === null)
        {
            return $varValue;
        }

        $arrColumns = array("id IN('" . implode("','", $arrChoosedTypes) . "')");

        $objObjectTypes = ObjectTypeModel::findBy($arrColumns, array());

        if($objObjectTypes !== null)
        {
            $arrOptions = array();

            while($objObjectTypes->next())
            {
                $arrOptions[ $objObjectTypes->id ] = $objObjectTypes->title;
            }

            $this->database->prepare("UPDATE tl_lead_matching SET objectTypesData=? WHERE id=?")
                           ->execute(serialize($arrOptions), $dc->id);
        }

        return $varValue;
    }

    /**
     * Save key value set of regions
     *
     * @param mixed         $varValue
     * @param DataContainer $dc
     *
     * @return string
     */
    public function saveRegions($varValue, DataContainer $dc): ?string
    {
        if(!$varValue || $dc->activeRecord->type !== LeadMatchingController::TYPE)
        {
            return $varValue;
        }

        $arrChoosedTypes = StringUtil::deserialize($varValue);

        if($arrChoosedTypes === null)
        {
            return $varValue;
        }

        $arrColumns = array("id IN('" . implode("','", $arrChoosedTypes) . "')");

        $objRegions = RegionModel::findBy($arrColumns, array());

        if($objRegions !== null)
        {
            $arrOptions = array();

            while($objRegions->next())
            {
                $arrOptions[ $objRegions->id ] = $objRegions->title;
            }

            $this->database->prepare("UPDATE tl_lead_matching SET regionsData=? WHERE id=?")
                           ->execute(serialize($arrOptions), $dc->id);
        }

        return $varValue;
    }

    /**
     * Returns an array of search criteria fields
     *
     * @return array
     */
    public function getListMetaFields(): array
    {
        $arrOptions = array();
        $arrSkip = array('published');

        Controller::loadDataContainer('tl_searchcriteria');

        foreach($GLOBALS['TL_DCA']['tl_searchcriteria']['fields'] as $key=>$opt)
        {
            if(!in_array($key, $arrSkip))
            {
                $arrOptions[] = $key;
            }
        }

        return $arrOptions;
    }

    /**
     * Return the edit form wizard
     *
     * @param DataContainer $dc
     *
     * @return string
     */
    public function editForm(DataContainer $dc): string
    {
        if ($dc->value < 1)
        {
            return '';
        }

        $title = sprintf($GLOBALS['TL_LANG']['tl_lead_matching']['editalias'], $dc->value);

        return ' <a href="contao/main.php?do=form&amp;table=tl_form_field&amp;id=' . $dc->value . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="' . StringUtil::specialchars($title) . '" onclick="Backend.openModalIframe({\'title\':\'' . StringUtil::specialchars(str_replace("'", "\\'", $title)) . '\',\'url\':this.href});return false">' . Image::getHtml('alias.svg', $title) . '</a>';
    }

    /**
     * Get all forms and return them as array
     *
     * @return array
     */
    public function getForms(): array
    {
        $arrForms = array();
        $objForms = $this->database->execute("SELECT id, title FROM tl_form ORDER BY title");

        while ($objForms->next())
        {
            $arrForms[$objForms->id] = $objForms->title . ' (ID ' . $objForms->id . ')';
        }

        return $arrForms;
    }
}
