const loginMessage = document.getElementById("login-message");


const form = document.querySelector("form");

if (window.loginErrors && window.loginErrors.length > 0) {
  alert(window.loginErrors.join("\n"));
}

form.addEventListener("submit", (event) => {
  const username = document.querySelector("#username");
  const password = document.querySelector("#password");

  if (username.value.trim() === "" || password.value.trim() === "") {
    event.preventDefault();

    alert("Please enter username and password.");
  }
});

if (loginMessage) {
  const message = loginMessage.dataset.message;

  alert(message);
}