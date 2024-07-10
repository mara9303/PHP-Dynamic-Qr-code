const phoneInputs = {
    phone: "phone_number",
    country: "phone_country_code"
};
const homeInputs = {
    phone: "home_phone_number",
    country: "home_phone_country_code"
};
const workInputs = {
    phone: "work_phone_number",
    country: "work_phone_country_code"
};
const configInputTel = (inputs)=>{
    return {
        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/utils.js",
        countryOrder: ["cr", "us"],
        strictMode: true,
        hiddenInput: function(telInputName) {
            return inputs;
        }
    }
}
const inputs = document.querySelectorAll(".phoneCode");
const inputsHome = document.querySelectorAll(".homePhoneCode");
const inputsWork = document.querySelectorAll(".workPhoneCode");
inputs.forEach((input)=>{
    window.intlTelInput(input, configInputTel(phoneInputs));
})
inputsHome.forEach((input)=>{
    window.intlTelInput(input, configInputTel(homeInputs));
})
inputsWork.forEach((input)=>{
    window.intlTelInput(input, configInputTel(workInputs));
})
