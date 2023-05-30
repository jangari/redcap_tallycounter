<?php

namespace INTERSECT\TallyCounter;

use ExternalModules\AbstractExternalModule;

class TallyCounter extends \ExternalModules\AbstractExternalModule {

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
}
