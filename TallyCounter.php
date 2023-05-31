<?php

namespace INTERSECT\TallyCounter;

use ExternalModules\AbstractExternalModule;

class TallyCounter extends \ExternalModules\AbstractExternalModule {

    // Output JavaScript to amend the action tags guide.
    // This code borrowed, with thanks, from Richard Dooley (https://github.com/richdooley)

	function provideActionTagExplain( $listActionTags )
	{
		if ( empty( $listActionTags ) )
		{
			return;
		}
		$listActionTagsJS = [];
		foreach ( $listActionTags as $t => $d )
		{
			$listActionTagsJS[] = [ $t, $d ];
		}
		$listActionTagsJS = json_encode( $listActionTagsJS );

?>
<script type="text/javascript">
$(function()
{
  var vActionTagPopup = actionTagExplainPopup
  var vMakeRow = function(vTag, vDesc, vTable)
  {
    var vRow = $( '<tr>' + vTable.find('tr:first').html() + '</tr>' )
    var vOldTag = vRow.find('td:eq(1)').html()
    var vButton = vRow.find('button')
    vRow.find('td:eq(1)').html(vTag)
    vRow.find('td:eq(2)').html(vDesc)
    if ( vButton.length != 0 )
    {
      vButton.attr('onclick', vButton.attr('onclick').replace(vOldTag,vTag))
    }
    var vRows = vTable.find('tr')
    var vInserted = false
    for ( var i = 0; i < vRows.length; i++ )
    {
      var vA = vRows.eq(i).find('td:eq(1)').html()
      if ( vTag < vRows.eq(i).find('td:eq(1)').html() )
      {
        vRows.eq(i).before(vRow)
        vInserted = true
        break
      }
    }
    if ( ! vInserted )
    {
      vRows.last().after(vRow)
    }
  }
  actionTagExplainPopup = function(hideBtns)
  {
    vActionTagPopup(hideBtns)
    var vCheckTagsPopup = setInterval( function()
    {
      if ( $('div[aria-describedby="action_tag_explain_popup"]').length == 0 )
      {
        return
      }
      clearInterval( vCheckTagsPopup )
      var vActionTagTable = $('#action_tag_explain_popup table');
      <?php echo $listActionTagsJS; ?>.forEach(function(vItem)
      {
        vMakeRow(vItem[0],vItem[1],vActionTagTable)
      })
    }, 200 )
  }
})
</script>
<?php

    }

    function getTags($tag,$instrument) {
        // This is straight out of Andy Martin's example post on this:
        // https://community.projectredcap.org/questions/32001/custom-action-tags-or-module-parameters.html
        if (!class_exists('INTERSECT\TallyCounter\ActionTagHelper')) include_once('classes/ActionTagHelper.php');
        $action_tag_results = ActionTagHelper::getActionTags($tag,null,$instrument);
        return $action_tag_results;
    }

    function add_tally_counter($instrument){

        // Build an array of @TALLY fields
        $tallyFields = $this->getTags("@TALLY",$instrument);

        // Exit if nothing to process
        if (empty($tallyFields)) {
            return;
        }

        // Encode as JS array for injection later
        $jsKeys = json_encode(array_keys($tallyFields['@TALLY']));

        // Echo the JavaScript code
        echo '<script>
            const incrementTally = function(name,n) {
                var initialValue = eval("document.form."+name+".value") || 0;
                var newValue = +initialValue+n;
                eval("document.form."+name+".value="+newValue+";");
                dataEntryFormValuesChanged = true;
                // Trigger branching/calc fields, in case fields affected
                $("[name=\'+name+\']").focus();
                setTimeout(function(){try{calculate(name);doBranching(name);}catch(e){}},50);
            }

            const keys = ' . $jsKeys . ';
            var tallyPlusBtn = document.createElement("button");
            tallyPlusBtn.classList.add("ui-widget");
            tallyPlusBtn.classList.add("jqbuttonsm");
            tallyPlusBtn.classList.add("ms-2");
            tallyPlusBtn.classList.add("ui-corner-all");
            tallyPlusBtn.classList.add("ui-widget");
            tallyPlusBtn.classList.add("tally-counter");
            tallyPlusBtn.classList.add("fas");
            var tallyMinusBtn = tallyPlusBtn.cloneNode();
            tallyPlusBtn.classList.add("tally-plus");
            tallyPlusBtn.classList.add("fa-plus");
            tallyMinusBtn.classList.add("tally-minus");
            tallyMinusBtn.classList.add("fa-minus");

        $(document).ready(function(){
            // Loop through the keys
            keys.forEach(function(key) {
                // Append copies of the plus and minus buttons to each @TALLY field
                var tallyField = $("input[name=\'"+key+"\']");
                var fv = tallyField.attr("fv");
                if(fv === "integer" || fv === "number"){

                    tallyField.parent().after(tallyPlusBtn.cloneNode()).after(tallyMinusBtn.cloneNode());

                    // Map the incrementTally function to each
                    tallyField.parent().parent().children("button.tally-plus").each(function() {
                        $(this).on("click",function(){
                            incrementTally(key,1);
                            return false;
                        });
                    });
                    tallyField.parent().parent().children("button.tally-minus").each(function() {
                        $(this).on("click",function(){
                            incrementTally(key,-1);
                            return false;
                        });
                    });
                } else {
                    console.warn("Tally Counter Error: Field \'"+key+"\' is not compatible due to its field validation type:",fv+". Accceptable field validation types are: integer, number.");
                };
            });
        });
    </script>';
    echo '<style>
        button.tally-counter {
              border: none;
              color: white;
              margin-top: 10px;
              padding: 4px 18px;
              text-align: center;
              text-decoration: none;
              display: inline-block;
              /* font-size: 16px; */
        }
        button.tally-plus {background-color: var(--bs-green);}
        button.tally-plus::before {font-size: 2em;}
        button.tally-minus {background-color: var(--bs-red);}
        button.tally-minus::before {font-size: 2em;}
        </style>';
    }

    function redcap_survey_page_top($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) {
        $this->add_tally_counter($instrument);
    }
    function redcap_data_entry_form_top($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance){
        $this->add_tally_counter($instrument);
    }
    function redcap_every_page_top( $project_id = null )
    {
    
		// Amend the list of action tags (accessible from the add/edit field window in the
		// instrument designer) when features which provide extra action tags are enabled.
        // This code borrowed, with thanks, from Richard Dooley (https://github.com/richdooley)

		if ( substr( PAGE_FULL, strlen( APP_PATH_WEBROOT ), 26 ) == 'Design/online_designer.php' ||
		     substr( PAGE_FULL, strlen( APP_PATH_WEBROOT ), 22 ) == 'ProjectSetup/index.php' )
		{
			$listActionTags = [];
			{
				$listActionTags['@TALLY'] =
					'Converts text entry fields with either number or integer validation to tally fields with a plus and minus button for incrementing and decrementing the field\'s value by 1.';
			}
			$this->provideActionTagExplain( $listActionTags );
		}
    }
}
