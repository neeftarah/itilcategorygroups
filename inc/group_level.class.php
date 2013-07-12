<?php

class PluginMeteofrancehelpdeskGroup_Level extends CommonDBChild {

   // From CommonDBChild
   public $itemtype = 'Group';
   public $items_id = 'groups_id';

   function getIndexName() {
      return $this->items_id;
   }

   static function getTypeName($nb=0) {
      global $LANG;
      return $LANG['plugin_meteofrancehelpdesk']['title'][9];
   }

   function canView() {
      return Session::haveRight('config', 'r');
   }
   
   function canCreate() {
      return Session::haveRight('config', 'w');
   }

   static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      return $DB->query("CREATE TABLE IF NOT EXISTS `$table` (
         `id`                int(11) NOT NULL auto_increment,
         `groups_id` int(11) NOT NULL,
         `level`             int(11) DEFAULT NULL,
         PRIMARY KEY (`id`),
         KEY         `groups_id` (`groups_id`),
         KEY         `level` (`level`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
   }

   static function uninstall() {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      return $DB->query("DROP TABLE IF EXISTS `$table`");
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'Group' :
               return $LANG['plugin_meteofrancehelpdesk']['title'][1];
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Group') {
         self::showForGroup($item);
      }
      return true;
   }

   static function showForGroup(Group $group) {
      global $DB, $LANG;

      $ID = $group->getField('id');
      if (!$group->can($ID,'r')) {
         return false;
      }

      $canedit = $group->can($ID,'w');
      if ($canedit) {
         // Get data
         $item = new self();
         if (!$item->getFromDB($ID)) {
            $item->getEmpty();
         }

         $rand = mt_rand();
         echo "<form name='group_level_form$rand' id='group_level_form$rand' method='post'
                action='";
         echo Toolbox::getItemTypeFormURL(__CLASS__)."'>";
         echo "<input type='hidden' name='".$item->items_id."' value='$ID' />";

         echo "<div class='spaced'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'><th>".$LANG['plugin_meteofrancehelpdesk']['title'][9].
              "</tr>";

         echo "<tr class='tab_bg_2'><td class='center'>";
         Dropdown::showFromArray('level', 
                                 array(NULL => "---",
                                       1    => $LANG['plugin_meteofrancehelpdesk']['title'][4],
                                       2    => $LANG['plugin_meteofrancehelpdesk']['title'][5],
                                       3    => $LANG['plugin_meteofrancehelpdesk']['title'][6],
                                       4    => $LANG['plugin_meteofrancehelpdesk']['title'][7]), 
                                 array('value' => $item->fields['level']));
         echo "</td></tr>";

         echo "</td><td class='center'>";
         if ($item->fields["id"]) {
            echo "<input type='hidden' name='id' value='".$item->fields["id"]."'>";
            echo "<input type='submit' name='update' value=\"".$LANG['buttons'][7]."\"
                   class='submit'>";
         } else {
            echo "<input type='submit' name='add' value=\"".$LANG['buttons'][7]."\" class='submit'>";
         }
         echo "</td></tr>";


         echo "</table></div>";
         Html::closeForm();
      }
   }

   static function getAddSearchOptions($itemtype) {
      global $LANG;

      $opt = array();

      if ($itemtype = 'Group') {
         $opt[9978]['table']      = getTableForItemType(__CLASS__);
         $opt[9978]['field']      = 'level';
         $opt[9978]['name']       = $LANG['plugin_meteofrancehelpdesk']['title'][9];
         $opt[9978]['linkfield']  = 'level';
         $opt[9978]['joinparams'] = array('jointype' => 'child');
      }

      return $opt;
   }

   static function getAllGroupForALevel($level) {
      global $DB;

      $groups_id = array();
      $query = "SELECT gl.groups_id 
                FROM ".getTableForItemType(__CLASS__)." gl 
                LEFT JOIN glpi_groups gr 
                    ON gl.groups_id = gr.id
                WHERE gl.level = $level".
                getEntitiesRestrictRequest(" AND ", "gr", 'entities_id',
                                           $_SESSION['glpiactive_entity'],
                                           $_SESSION['glpiactive_entity_recursive']);
      foreach   ($DB->request($query) as $data) {
         $groups_id[] = $data['groups_id'];
      }
      return $groups_id;
   }
}