<?php
/**
 * This file is part of
 * Kimai - Open Source Time Tracking // http://www.kimai.org
 * (c) 2006-2009 Kimai-Development-Team
 *
 * Kimai is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; Version 3, 29 June 2007
 *
 * Kimai is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kimai; If not, see <http://www.gnu.org/licenses/>.
 */

// ================
// = TS PROCESSOR =
// ================

// insert KSPI
$isCoreProcessor = 0;
$dir_templates = "templates/";
require("../../includes/kspi.php");

// ==================
// = handle request =
// ==================
switch ($axAction) {

    // =========================
    // = record an event AGAIN =
    // =========================
    case 'record':
        if (isset($kga['customer'])) die();

        $zefData = $database->zef_get_data($id);

        $zefData['in'] = time();
        $zefData['out'] = 0;
        $zefData['diff'] = 0;

        // copied from check_zef_data and inverted assignments
        $zefData['pct_ID'] = $zefData['zef_pctID'];
        $zefData['evt_ID'] = $zefData['zef_evtID'];
        $zefData['zlocation'] = $zefData['zef_location'];
        $zefData['trackingnr'] = $zefData['zef_trackingnr'];
        $zefData['description'] = $zefData['zef_description'];
        $zefData['comment'] = $zefData['zef_comment'];
        $zefData['comment_type'] = $zefData['zef_comment_type'];
        $zefData['rate'] = $zefData['zef_rate'];
        $zefData['cleared'] = $zefData['zef_cleared'];
        //fcw: status hatte hier noch gefehlt
        $zefData['status'] = $zefData['zef_status'];
        $zefData['usr_ID'] = $kga['usr']['usr_ID'];

        $newZefId = $database->zef_create_record($zefData);

        $usrData = array();
        $usrData['lastRecord'] = $newZefId;
        $usrData['lastProject'] = $zefData['pct_ID'];
        $usrData['lastEvent'] = $zefData['evt_ID'];
        $database->usr_edit($kga['usr']['usr_ID'], $usrData);


        $pctdata = $database->pct_get_data($zefData['zef_pctID']);
        $return =  'pct_name = "' . $pctdata['pct_name'] .'"; ';

        $return .=  'knd = "' . $pctdata['pct_kndID'] .'"; ';

        $knddata = $database->knd_get_data($pctdata['pct_kndID']);
        $return .=  'knd_name = "' . $knddata['knd_name'] .'"; ';

        $evtdata = $database->evt_get_data($zefData['zef_evtID']);
        $return .= 'evt_name = "' . $evtdata['evt_name'] .'"; ';
        
        $return .= "currentRecording = $newZefId; ";

        echo $return;
        // TODO return false if error
    break;

    // ==================
    // = stop recording =
    // ==================
    case 'stop':
        if (isset($kga['customer'])) die();
        $database->stopRecorder($id);
        echo 1;
    break;

    // ===================================
    // = set comment for a running event =
    // ===================================
    case 'edit_running_project':
        if (isset($kga['customer'])) die();

        $database->zef_edit_pct(
            $_REQUEST['id'],
            $_REQUEST['project']);
        echo 1;
    break;

    // ===================================
    // = set comment for a running event =
    // ===================================
    case 'edit_running_task':
        if (isset($kga['customer'])) die();

        $database->zef_edit_evt(
            $_REQUEST['id'],
            $_REQUEST['task']);
        echo 1;
    break;




    // TODO 5 -o fcw -c BugFix:No search result but an icon to edit is displayed anyway, but shouldn't
    case 'search_event_comment':
        $searchwords = $_GET['search'];
        $searchwords = str_replace('--', ' -', $searchwords);
        $result = $database->zef_search_event_comment($searchwords);
        
        if (0 < count($result))
        {
            for ($i = 0; $i < count($result); $i++)
            {
                $id = $result[$i][0];
                $time = $result[$i][1];
                $comment = substr($result[$i][2], 0, $kga['conf']['searchMaxResult']);
                // The searched result is highlighted in between the whole comment with <span class="highlighted" (or similar)
                echo '<p class="search_result">';
                echo '<a class="search_result_link" title="'.$kga['lang']['edit'].'" onclick="editRecord('.$id.'); $(this).blur(); return false;" href="#"><img width="13" height="13" border="0" title="'.$kga['lang']['edit'].'" alt="'.$kga['lang']['edit'].' (ID:'.$id.')" src="../skins/standard/grfx/edit2.gif">';   
                echo ' <span class="search_result_time"> '.$time.' </span> ';
                echo preg_replace('/(' . str_replace('+', '\+', $searchwords) . ')/Usi', '<span class="search_result_highlighted">\\1</span>', $comment);
                echo '</a> </p>';
            }
        }
        else
        {
            echo '<p class="search_result">';
            echo $kga['lang']['noSearchResult'];  
            echo '</span>';
        }
    break;
    

    // =========================================
    // = Erase timesheet entry via quickdelete =
    // =========================================
    case 'quickdelete':
        $database->zef_delete_record($id);
        echo 1;
    break;

    // ===============================================
    // = Get the best rate for the project and event =
    // ===============================================
    case 'bestFittingRates':
        if (isset($kga['customer'])) die();

        $data = array(
          'hourlyRate' => $database->get_best_fitting_rate($kga['usr']['usr_ID'],$_REQUEST['project_id'],$_REQUEST['event_id']),
          'fixedRate' => $database->get_best_fitting_fixed_rate($_REQUEST['project_id'],$_REQUEST['event_id'])
        );
        echo json_encode($data);
    break;


    // ===============================================
    // = Get the new budget data after changing project or event =
    // ===============================================
    case 'budgets':
        if (isset($kga['customer'])) die();
        $zefData = $database->zef_get_data($_REQUEST['zef_id']);
        // we subtract the used data in case the event is the same as in the db, otherwise
        // it would get counted twice. For all aother cases, just set the values to 0
        // so we don't subtract too much
        if($zefData['zef_evtID'] != $_REQUEST['event_id'] || $zefData['zef_pctID'] != $_REQUEST['project_id']) {
        	$zefData['zef_budget'] = 0;
        	$zefData['zef_approved'] = 0;
        	$zefData['zef_rate'] = 0;
        }
        $data = array(
          'eventBudgets' => $database->get_evt_budget($_REQUEST['project_id'],$_REQUEST['event_id']),
          'eventUsed' => $database->get_budget_used($_REQUEST['project_id'],$_REQUEST['event_id']),
          'zefData' => $zefData
        );
        echo json_encode($data);
    break;

    // ===========================================
    // = Get all rates for the project and event =
    // ===========================================
    case 'allFittingRates':
        if (isset($kga['customer'])) die();

        $rates = $database->allFittingRates($kga['usr']['usr_ID'],$_REQUEST['project'],$_REQUEST['task']);
        $processedData = array();

        if ($rates !== false)
          foreach ($rates as $rate) {
            $line = Format::formatCurrency($rate['rate']);

            $setFor = array(); // contains the list of "types" for which this rate was set
            if ($rate['user_id'] != null)
              $setFor[] = $kga['lang']['username'];
            if ($rate['project_id'] != null)
              $setFor[] =  $kga['lang']['pct'];
            if ($rate['event_id'] != null)
              $setFor[] =  $kga['lang']['evt'];

            if (count($setFor) != 0)
              $line .= ' ('.implode($setFor,', ').')';

            $processedData[] = array('value'=>$rate['rate'], 'desc'=>$line);
          }

        echo json_encode($processedData);
    break;

    // ===========================================
    // = Get all rates for the project and event =
    // ===========================================
    case 'allFittingFixedRates':
        if (isset($kga['customer'])) die();

        $rates = $database->allFittingFixedRates($_REQUEST['project'],$_REQUEST['task']);
        $processedData = array();

        if ($rates !== false)
          foreach ($rates as $rate) {
            $line = Format::formatCurrency($rate['rate']);

            $setFor = array(); // contains the list of "types" for which this rate was set
            if ($rate['project_id'] != null)
              $setFor[] =  $kga['lang']['pct'];
            if ($rate['event_id'] != null)
              $setFor[] =  $kga['lang']['evt'];

            if (count($setFor) != 0)
              $line .= ' ('.implode($setFor,', ').')';

            $processedData[] = array('value'=>$rate['rate'], 'desc'=>$line);
          }

        echo json_encode($processedData);
    break;

    // ===============================================
    // = Get the best rate for the project and event =
    // ===============================================
    case 'reload_evt_options':
        if (isset($kga['customer'])) die();
        $arr_evt = $database->get_arr_evt_by_pct($_REQUEST['pct'],$kga['usr']['groups']);
        foreach ($arr_evt as $event) {
          if (!$event['evt_visible'])
            continue;
          echo '<option value="'.$event['evt_ID'].'">'.
          $event['evt_name'].'</option>\n';
        }
    break;

    // ===================================================
    // = Load timesheet data (zef) from DB and return it =
    // ===================================================
    case 'reload_zef':
        $filters = explode('|',$axValue);
        if ($filters[0] == "")
          $filterUsr = array();
        else
          $filterUsr = explode(':',$filters[0]);

        if ($filters[1] == "")
          $filterKnd = array();
        else
          $filterKnd = explode(':',$filters[1]);

        if ($filters[2] == "")
          $filterPct = array();
        else
          $filterPct = explode(':',$filters[2]);

        if ($filters[3] == "")
          $filterEvt = array();
        else
          $filterEvt = explode(':',$filters[3]);

        // if no userfilter is set, set it to current user
        if (isset($kga['usr']) && count($filterUsr) == 0)
          array_push($filterUsr,$kga['usr']['usr_ID']);

        if (isset($kga['customer']))
          $filterKnd = array($kga['customer']['knd_ID']);

        $arr_zef = $database->get_arr_zef($in,$out,$filterUsr,$filterKnd,$filterPct,$filterEvt,1);
        if (count($arr_zef)>0) {
            $tpl->assign('arr_zef', $arr_zef);
        } else {
            $tpl->assign('arr_zef', 0);
        }
        $tpl->assign('total', Format::formatDuration($database->get_zef_time($in,$out,$filterUsr,$filterKnd,$filterPct,$filterEvt)));

        $ann = $database->get_arr_time_usr($in,$out,$filterUsr,$filterKnd,$filterPct,$filterEvt);
        Format::formatAnnotations($ann);
        $tpl->assign('usr_ann',$ann);

        $ann = $database->get_arr_time_knd($in,$out,$filterUsr,$filterKnd,$filterPct,$filterEvt);
        Format::formatAnnotations($ann);
        $tpl->assign('knd_ann',$ann);

        $ann = $database->get_arr_time_pct($in,$out,$filterUsr,$filterKnd,$filterPct,$filterEvt);
        Format::formatAnnotations($ann);
        $tpl->assign('pct_ann',$ann);

        $ann = $database->get_arr_time_evt($in,$out,$filterUsr,$filterKnd,$filterPct,$filterEvt);
        Format::formatAnnotations($ann);
        $tpl->assign('evt_ann',$ann);

        if (isset($kga['usr']))
          $tpl->assign('hideComments',$database->usr_get_preference('ui.showCommentsByDefault')!=1);
        else
          $tpl->assign('hideComments',true);

        if (isset($kga['usr']))
          $tpl->assign('showOverlapLines',$database->usr_get_preference('ui.hideOverlapLines')!=1);
        else
          $tpl->assign('showOverlapLines',false);

        $tpl->display("zef.tpl");
    break;


    // =========================
    // = add / edit zef record =
    // =========================
    case 'add_edit_record':
      if (isset($kga['customer'])) die();

      if ($id) {
        $data = $database->zef_get_data($id);
        if ($kga['conf']['editLimit'] != "-" && time()-$data['zef_out'] > $kga['conf']['editLimit']) {
          echo json_encode(array('result'=>'error','message'=>$kga['lang']['editLimitError']));
          return;
        }
      }

      if (isset($_REQUEST['erase'])) {
        // delete checkbox set ?
        // then the record is simply dropped and processing stops at this point
          $database->zef_delete_record($id);
          echo json_encode(array('result'=>'ok'));
          break;
      }

      $data['pct_ID']          = $_REQUEST['pct_ID'];
      $data['evt_ID']          = $_REQUEST['evt_ID'];
      $data['zlocation']       = $_REQUEST['zlocation'];
      $data['trackingnr']      = $_REQUEST['trackingnr'];
      $data['description']     = $_REQUEST['description'];
      $data['comment']         = $_REQUEST['comment'];
      $data['comment_type']    = $_REQUEST['comment_type'];
      $data['rate']            = str_replace($kga['conf']['decimalSeparator'],'.',$_REQUEST['rate']);
      $data['fixed_rate']      = str_replace($kga['conf']['decimalSeparator'],'.',$_REQUEST['fixed_rate']);
      $data['cleared']         = isset($_REQUEST['cleared']);
      $data['status']          = $_REQUEST['status'];
      $data['billable']        = $_REQUEST['billable'];
      $data['budget']          = str_replace($kga['conf']['decimalSeparator'],'.',$_REQUEST['budget']);
      $data['approved']        = str_replace($kga['conf']['decimalSeparator'],'.',$_REQUEST['approved']);

      // only take the given user id if it is in the list of watchable users
      $users = $database->get_arr_watchable_users($kga['usr']);
      foreach ($users as $user) {
        if ($user['usr_ID'] == $_REQUEST['user']) {
          $data['zef_usrID'] = $user['usr_ID'];
          break;
        }
      }

      if (!isset($data['zef_usrID']))
        $data['zef_usrID'] = $kga['usr']['usr_ID'];

      // check if the posted time values are possible

      $validateDate = new Zend_Validate_Date(array('format' => 'dd.MM.yyyy'));
      $validateTime = new Zend_Validate_Date(array('format' => 'HH:mm:ss'));

      if (!$validateDate->isValid($_REQUEST['edit_in_day']) ||
          !$validateTime->isValid($_REQUEST['edit_in_time'])) {
        echo json_encode(array('result'=>'error','message'=>$kga['lang']['TimeDateInputError']));
          return;
      }

      if ( ($_REQUEST['edit_out_day'] != '' || $_REQUEST['edit_out_time'] != '') && (
          !$validateDate->isValid($_REQUEST['edit_in_day']) ||
          !$validateTime->isValid($_REQUEST['edit_in_time']))) {
        echo json_encode(array('result'=>'error','message'=>$kga['lang']['TimeDateInputError']));
          return;
      }

      $edit_in_day = Zend_Locale_Format::getDate($_REQUEST['edit_in_day'],
                                          array('date_format' => 'dd.MM.yyyy'));
      $edit_in_time = Zend_Locale_Format::getTime($_REQUEST['edit_in_time'],
                                          array('date_format' => 'HH:mm:ss'));

      $edit_in = array_merge($edit_in_day, $edit_in_time);

      $inDate = new Zend_Date($edit_in);

      if ($_REQUEST['edit_out_day'] != '' || $_REQUEST['edit_out_time'] != '') {
        $edit_out_day = Zend_Locale_Format::getDate($_REQUEST['edit_out_day'],
                                            array('date_format' => 'dd.MM.yyyy'));
        $edit_out_time = Zend_Locale_Format::getTime($_REQUEST['edit_out_time'],
                                            array('date_format' => 'HH:mm:ss'));

        $edit_out = array_merge($edit_out_day, $edit_out_time);

        $outDate = new Zend_Date($edit_out);
      }
      else {
        $outDate = null;
      }

      $data['in']   = $inDate->getTimestamp();

      if ($outDate != null) {
        $data['out']  = $outDate->getTimestamp();
        $data['diff'] = $data['out'] - $data['in'];
      }

      if ($id) { // TIME RIGHT - NEW OR EDIT ?

          // TIME RIGHT - EDIT ENTRY
          Logger::logfile("zef_edit_record: " .$id);
          check_zef_data($id,$data);

      } else {

          // TIME RIGHT - NEW ENTRY
          Logger::logfile("zef_create_record");
          $database->zef_create_record($data);
      }

      echo json_encode(array('result'=>'ok'));

    break;

}

?>