{literal}    
<script type="text/javascript"> 
  $(document).ready(function() {

    var options = { 
      beforeSubmit:  function() { 

        if ( ($('#password').val() != "" || $('#retypePassword').val() != "")
              && !validatePassword($('#password').val(),$('#retypePassword').val()))
          return false;
      },
      success: function() {
        window.location.reload();
      }
    }; 
    
    $('#core_prefs').ajaxForm(options); 
    $('#floater_innerwrap').tabs({ selected: 0 });

  }); 
</script>
<script type="text/javascript">$(function() {$('*').tooltip();});</script>
{/literal}
<!-- fcw: 2012-04-09: ToolTips
um ein Tooltip hinzuzufuegen, muss man dem Element einfach ein title-Attribut beigeben, das wird
dann als Tooltip verwendet. 
Die Lib ist das jquery.tooltip.js, die wird in:
/core/templates/core/main.tpl
eingebunden.
Gestylt werden die Tooltips in:
/core/skins/standard/jquery.tooltip.css
-->
<div id="floater_innerwrap">
    
  <div id="floater_handle">
    <span id="floater_title">{$kga.lang.preferences}</span>
    <div class="right">
      <a href="#" class="close" onClick="floaterClose();">{$kga.lang.close}</a>
    </div>       
  </div>


  <div class="menuBackground">

    <ul class="menu tabSelection">
      <li class="tab norm"><a href="#prefGeneral">
        <span class="aa">&nbsp;</span>
        <span class="bb">{$kga.lang.general}</span>
        <span class="cc">&nbsp;</span>
        </a>
      </li>
      <li class="tab norm"><a href="#prefSublists">
        <span class="aa">&nbsp;</span>
        <span class="bb">{$kga.lang.sublists}</span>
        <span class="cc">&nbsp;</span>
        </a>
      </li>
      <li class="tab norm"><a href="#prefList">
        <span class="aa">&nbsp;</span>
        <span class="bb">{$kga.lang.list}</span>
        <span class="cc">&nbsp;</span>
        </a>
      </li>
      <li class="tab norm"><a href="#search">
        <span class="aa">&nbsp;</span>
        <span class="bb">{$kga.lang.search}</span>
        <span class="cc">&nbsp;</span>
        </a>
      </li>      
    </ul>
  </div>

  <form id="core_prefs" action="processor.php" method="post"> 

    <div id="floater_tabs" class="floater_content">

      <fieldset id ="prefGeneral">
        <ul>
          <li>
            <label for="skin">{$kga.lang.skin}:</label>
            <select name="skin">
              {html_options values=$skins output=$skins selected=$kga.conf.skin}
            </select>
          </li>

          <li>
            <label for="pw">{$kga.lang.newPassword}:</label>
            <input type="password" name="pw" size="15" id="password" /> {$kga.lang.minLength}
          </li>

          <li>
            <label for="pw">{$kga.lang.retypePassword}:</label>
            <input type="password" name="retypePassword" size="15" id="retypePassword" />
          </li>

          <li>
            <label for="rate">{$kga.lang.my_rate}:</label>
            <input type="text" name="rate" size="9" value="{$rate|replace:'.':$kga.conf.decimalSeparator}"/>
          </li>

          <li>
            <label for="lang">{$kga.lang.lang}:</label>
            <select name="lang">
              {html_options values=$langs output=$langs selected=$kga.conf.lang}
            </select>
          </li>

          <li>
            <label for="timezone">{$kga.lang.timezone}:</label>
            <select name="timezone">
              {html_options values=$timezones output=$timezones selected=$kga.conf.timezone}
            </select>
          </li>

          <li>
            <label for="autoselection"></label>
            <input type="checkbox" name="autoselection" value="1" {if $kga.conf.autoselection}checked{/if} /> {$kga.lang.autoselection}
          </li>
          <li>
            <label for="showInstallWarning"></label>
            <input type="checkbox" name="showInstallWarning" value="1" {if $kga.conf.showInstallWarning}checked{/if} /> {$kga.lang.showInstallWarning}
          </li>          
        </ul>

      </fieldset>
        
      <fieldset id="prefSublists">
        <ul>
          <li>
            {$kga.lang.sublistAnnotations}:
            <select name="sublistAnnotations">
              <option value="0" {if $kga.conf.sublistAnnotations==0}selected="selected"{/if}>{$kga.lang.timelabel}</option>
              <option value="1" {if $kga.conf.sublistAnnotations==1}selected="selected"{/if}>{$kga.lang.xp_ext.costs}</option>
              <option value="2" {if $kga.conf.sublistAnnotations==2}selected="selected"{/if}>{$kga.lang.timelabel} &amp; {$kga.lang.xp_ext.costs}</option>
            </select>
          </li>

          <li>
            <label for="flip_pct_display"></label>
            <input type="checkbox" name="flip_pct_display" value="1" {if $kga.conf.flip_pct_display}checked{/if} /> {$kga.lang.flip_pct_display}
          </li>
          <li>
            <label for="pct_comment_flag"></label>
            <input type="checkbox" name="pct_comment_flag" value="1" {if $kga.conf.pct_comment_flag}checked{/if} /> {$kga.lang.pct_comment_flag}
          </li>
          <li>
            <label for="showIDs"></label>
            <input type="checkbox" name="showIDs" value="1" {if $kga.conf.showIDs}checked{/if} /> {$kga.lang.showIDs}
          </li>
          <li>
            <label for="noFading"></label>
            <input type="checkbox" name="noFading" value="1" {if $kga.conf.noFading}checked{/if} /> {$kga.lang.noFading}
          </li>
          <li>
            <label for="user_list_hidden"></label>
            <input type="checkbox" name="user_list_hidden" value="1" {if $kga.conf.user_list_hidden}checked{/if} /> {$kga.lang.user_list_hidden}
          </li>
        </ul>
      </fieldset>

      <fieldset id="prefList">
        <ul>
          <li>
            <label for="rowlimit">{$kga.lang.rowlimit}:</label>
            <input type="text" name="rowlimit" value="{$kga.conf.rowlimit}" size="9" />
          </li>
          <li>
            <label for="hideClearedEntries"></label>
            <input type="checkbox" name="hideClearedEntries" value="1" {if $kga.conf.hideClearedEntries}checked{/if} /> {$kga.lang.hideClearedEntries}
          </li>
          <li>
            {$kga.lang.quickdelete}:
            <select name="quickdelete">
              <option value="0" {if $kga.conf.quickdelete==0}selected="selected"{/if}>{$kga.lang.quickdeleteHide}</option>
              <option value="1" {if $kga.conf.quickdelete==1}selected="selected"{/if}>{$kga.lang.quickdeleteShow}</option>
              <option value="2" {if $kga.conf.quickdelete==2}selected="selected"{/if}>{$kga.lang.quickdeleteShowConfirm}</option>
            </select>
          </li>
          <li>
            <label for="showCommentsByDefault"></label>
            <input type="checkbox" name="showCommentsByDefault" value="1" {if $kga.conf.showCommentsByDefault}checked{/if} /> {$kga.lang.showCommentsByDefault}
          </li>     
          <li>
            <label for="hideOverlapLines"></label>
            <input type="checkbox" name="hideOverlapLines" value="1" {if $kga.conf.hideOverlapLines}checked{/if} /> {$kga.lang.hideOverlapLines}
          </li>     
        </ul>
      </fieldset>
      
      <fieldset id="search">
        <ul>
          <li>
            <label for="searchMin">{$kga.lang.searchMin}:</label>
            <input title="{$kga.lang.searchMinTT}" type="text" name="searchMin" value="{$kga.conf.searchMin}" size="2" />
          </li>     
          <li>
            <label for="searchMaxResult">{$kga.lang.searchMaxResult}:</label>
            <input title="{$kga.lang.searchMaxResultTT}" type="text" name="searchMaxResult" value="{$kga.conf.searchMaxResult}" size="4" />
          </li>     
        </ul>
      </fieldset>      
    </div>
          
    <input name="axAction" type="hidden" value="editPrefs" />   
    <input name="id" type="hidden" value="0" />   
                  
    <div id="formbuttons">
      <input class='btn_norm' type='button' value='{$kga.lang.cancel}' onClick='floaterClose(); return false;' />
      <input class='btn_ok' type='submit' value='{$kga.lang.submit}' />
    </div>

  </form>

</div>
