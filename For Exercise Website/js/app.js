// app.js - only the page and the buttons live here.
// The math is done by computeResult() in grades.js.

var form = document.getElementById("grade-form");
var nameBox = document.getElementById("student-name");
var clearButton = document.getElementById("clear-btn");
var resultBox = document.getElementById("result");

// Show a message in the result area with a color class
// (passed, failed, inc, or error)
function showMessage(message, colorClass) {
  resultBox.textContent = message;
  resultBox.className = "result " + colorClass;
}

// When the teacher clicks Compute (or presses Enter)
form.addEventListener("submit", function (event) {
  // Stop the page from reloading
  event.preventDefault();

  // Use a placeholder if no name was typed
  var name = nameBox.value.trim();
  if (name === "") {
    name = "Student";
  }

  // Collect the four quarter grades
  var grades = [
    document.getElementById("q1").value,
    document.getElementById("q2").value,
    document.getElementById("q3").value,
    document.getElementById("q4").value
  ];

  var result = computeResult(grades);

  if (result.status === "ERROR") {
    showMessage("Grades must be numbers from 0 to 100.", "error");
  } else if (result.status === "INC") {
    showMessage(name + ": INC (a quarter grade is missing)", "inc");
  } else {
    showMessage(
      name + ": Final Grade " + result.finalGrade.toFixed(2) + ": " + result.status,
      result.status.toLowerCase()
    );
  }
});

// When the teacher clicks Clear, empty everything
clearButton.addEventListener("click", function () {
  form.reset();
  resultBox.textContent = "";
  resultBox.className = "result";
  nameBox.focus();
});
