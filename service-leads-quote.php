<?php
global $table_prefix, $wpdb;
$servicesTable = $table_prefix . 'services';
$services_que = $table_prefix . 'service_questions';
$lead_table = $table_prefix . 'service_leads';

$servicedata = $wpdb->get_results("SELECT * FROM $servicesTable");

if(isset($_POST['get_quote'])){
    //echo "<pre>"; print_r($_POST); echo "</pre>";

    $serviceQ = $_POST['service_question'];
    $serviceA = $_POST['question_ans'];

    //echo "<pre>"; print_r($serviceQ); echo "</pre>";
   // echo "<pre>"; print_r($serviceA); echo "</pre>";

    $final_que = implode("|", $serviceQ);
    $final_ans = implode("|", $serviceA);
    //echo "<pre>"; print_r($final_que); echo "</pre>";

    $wpdb->insert($lead_table, array(
        'first_name' => $_POST['lead_fname'],
        'last_name' => $_POST['lead_lname'],
        'email' => $_POST['lead_email'],
        'phone_number' => $_POST['lead_number'],
        'service_name' => $_POST['service'],
        'service_question' => $final_que,
        'service_answers' => $final_ans,
    ));
}
?>
<section class="additional-services">
    <div class="tm-container">
    <div class="heading">
        <h2>While you wait, see below additional services that have been very popular within our network recently.</h2>
    </div>
    <div class="services-wrap">
        <div class="tm-row">
        <?php foreach($servicedata as $services){
        $serviceID = $services->tag_id;
        $serviceQ = $wpdb->get_results("SELECT * FROM $services_que WHERE service_id = '$serviceID'"); 
        if(!empty($serviceQ)){
        ?>
        <div class="tm-col-3">
            <div class="service-box">
            <form method="POST">
                <input type="hidden" class="lead_fname" name="lead_fname" value="">
                <input type="hidden" class="lead_lname" name="lead_lname" value="">
                <input type="hidden" class="lead_email" name="lead_email" value="">
                <input type="hidden" class="lead_number" name="lead_number" value="">
                <div class="head">
                <label class="tm-radio">
                    <input type="radio" value="<?php echo $services->service_name;?>" name="service"/>
                    <span class="custom-radio"></span>
                    <span class="text"><?php echo $services->service_name;?></span>
                </label>
                </div>
                <div class="body">
                <!-- Question loop start here -->
                <?php foreach($serviceQ as $question){ 
                $answerOpt = $question->answers;
                $answers = explode("|", $answerOpt);
                // echo "<pre>"; print_r($answers); echo "</pre>";
                ?>
                <div class="select-wrap">
                    <input type="hidden" class="service_question" name="service_question[]" value="<?php echo $question->question_title; ?>">
                    <label><?php echo $question->question_title;?></label>
                    <select class="question_ans" name="question_ans[]">
                    <option value="" disabled selected>Select an option</option>
                    <?php foreach($answers as $ans){ ?>
                    <option value="<?php echo trim($ans);?>"><?php echo trim($ans);?></option>
                    <?php } ?>
                    </select>
                </div>
                <?php } ?>
                <!-- Question loop ends here -->
                <div class="submit-btn">
                    <button type="submit" name="get_quote">Get Free Quote</button>
                </div>
                <div class="terms">
                    <p><?php echo $services->service_description;?></p>
                </div>
                </div>
            </form>
            </div>
        </div>
        <?php } } ?>
        </div>
    </div>
    </div>
</section>