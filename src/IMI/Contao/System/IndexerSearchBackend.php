<?php

namespace IMI\Contao\System;


/**
 * @category IMI
 * @package _
 */
class IndexerSearchBackend extends Backend {
    /**
     * Mostly copied from \Contao\Automator::generateSitemap
     * @param int $intId
     */
    public function getSearchablePages()
    {
        $time = \Date::floorToMinute();
        $objDatabase = \Database::getInstance();

        // Get all published root pages
        $objRoot = $objDatabase->execute("SELECT id, language, sitemapName FROM tl_page WHERE type='root' AND createSitemap='1' AND sitemapName!='' AND (start='' OR start<='$time') AND (stop='' OR stop>'" . ($time + 60) . "') AND published='1'");

        // Return if there are no pages
        if ($objRoot->numRows < 1)
        {
            return;
        }

        $result = array();

        // Create the XML file
        while ($objRoot->next())
        {
            $objFile = new \File('share/' . $objRoot->sitemapName . '.xml', true);

            // Find the searchable pages
            $arrPages = \Backend::findSearchablePages($objRoot->id, '', true);

            // HOOK: take additional pages
            if (isset($GLOBALS['TL_HOOKS']['getSearchablePages']) && is_array($GLOBALS['TL_HOOKS']['getSearchablePages']))
            {
                foreach ($GLOBALS['TL_HOOKS']['getSearchablePages'] as $callback)
                {
                    $this->import($callback[0]);
                    $arrPages = $this->{$callback[0]}->{$callback[1]}($arrPages, $objRoot->id, true, $objRoot->language);
                }
            }

            // Add pages
            foreach ($arrPages as $strUrl)
            {
                $result[] = $strUrl;
            }
        }

        return $result;
    }
}