
// Verification of Inscription form
const form =document.querySelector(".register-form")
const inputName=document.querySelector(".js-input-name")
const inputEmail=document.querySelector(".js-input-email")
const inputPwd=document.querySelector(".js-input-pwd")
const inputConfirm = document.querySelector(".js-input-confirm")

const REGEX={
     // Minimum 3 caractères
  regexUsername:/^.{3,}$/,
   // Format email valide
  regexEmail: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
  // Min. 8 caractères : minuscule, majuscule, chiffre et caractère spécial
  regexPwd:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/
}

// Fonction pour changer l'état du champ

function setValidation(input, isValid) {
  const field = input.closest(".field");
  if (isValid) {
      field.classList.add("valid");
      field.classList.remove("invalid");
  } else {
      field.classList.add("invalid");
      field.classList.remove("valid");
  }
}

// NAME

inputName.addEventListener("keyup", function () {
  const isValid = REGEX.regexUsername.test(this.value);
  setValidation(this, isValid);
});
// EMAIL

inputEmail.addEventListener("keyup", function () {
  const isValid = REGEX.regexEmail.test(this.value);
  setValidation(this, isValid);
});

// PASSWORD
inputPwd.addEventListener("keyup", function () {
  const isValid = REGEX.regexPwd.test(this.value);
  setValidation(this, isValid);
  // Recheck confirmation
  if (inputConfirm.value !== "") {
      setValidation(
          inputConfirm,
          inputConfirm.value === inputPwd.value
      );
  }
});

// CONFIRM PASSWORD

inputConfirm.addEventListener("keyup", function () {
  const isValid =this.value !== "" &&
      this.value === inputPwd.value;
  setValidation(this, isValid);
});


// Message registration 
const successMessage = document.querySelector(".register-success");
if (successMessage) {
    setTimeout(() => {
        successMessage.remove();
    }, 3000);

}