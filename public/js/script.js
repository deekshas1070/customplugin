jQuery(".ff_btn_style").on("click", function(){
  var first_name = jQuery("#ff_3_names_first_name_").val();
  var last_name = jQuery("#ff_3_names_last_name_").val();
  var email = jQuery("#ff_3_email").val();
  var phone_number = jQuery("#ff_3_phone").val();

  sessionStorage.setItem("lead_first_name", first_name);
  sessionStorage.setItem("lead_last_name", last_name);
  sessionStorage.setItem("lead_email", email);
  sessionStorage.setItem("lead_number", phone_number);
});

var lead_name = sessionStorage.getItem("lead_first_name");
var lead_last_name = sessionStorage.getItem("lead_last_name");
var lead_email = sessionStorage.getItem("lead_email");
var lead_number = sessionStorage.getItem("lead_number");
if(lead_name != null && lead_last_name != null && lead_email != null && lead_number != null){
  jQuery(".lead_fname").val(lead_name);
  jQuery(".lead_lname").val(lead_last_name);
  jQuery(".lead_email").val(lead_email);
  jQuery(".lead_number").val(lead_number);
}


let allServiceBoxes = document.querySelectorAll(".service-box");
let allServiceBoxRadios = document.querySelectorAll(
  ".service-box .tm-radio input[type='radio']"
);

allServiceBoxRadios.forEach((currBtn) => {
  currBtn.addEventListener("change", function () {
    for (const serviceRadio of allServiceBoxRadios) {
      serviceRadio.checked = false;
    }
    this.checked = true;

    for (const serviceBox of allServiceBoxes) {
      serviceBox.classList.remove("active");
    }
    let currServiceBox = this.closest(".service-box");
    currServiceBox.classList.add("active");
  });
});




