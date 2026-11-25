// grades.js - only the math and the DepEd rules live here.
// It does not touch the web page.

// The lowest grade that counts as PASSED (DepEd rule)
var PASSING_GRADE = 75;

// Check one grade that came from a box.
// Returns "empty", "invalid", or "ok".
function checkGrade(text) {
  // Remove extra spaces so "   " counts as empty
  if (String(text).trim() === "") {
    return "empty";
  }
  var number = Number(text);
  // Not a number, or outside 0 to 100, is invalid
  if (isNaN(number) || number < 0 || number > 100) {
    return "invalid";
  }
  return "ok";
}

// Take a list of four grades (as text) and return an answer object:
//   { status: "INC" }                          if any quarter is empty
//   { status: "ERROR" }                        if any grade is not 0 to 100
//   { status: "PASSED" or "FAILED", finalGrade: number }
function computeResult(grades) {
  var hasEmpty = false;
  var hasInvalid = false;

  for (var i = 0; i < grades.length; i++) {
    var check = checkGrade(grades[i]);
    if (check === "empty") {
      hasEmpty = true;
    } else if (check === "invalid") {
      hasInvalid = true;
    }
  }

  // A bad number is reported first so the teacher can fix it
  if (hasInvalid) {
    return { status: "ERROR" };
  }

  // Any empty quarter means INC (incomplete) and no final grade
  if (hasEmpty) {
    return { status: "INC" };
  }

  // Add the four grades together
  var total = 0;
  for (var j = 0; j < grades.length; j++) {
    total = total + Number(grades[j]);
  }

  // Average of the quarters, rounded to 2 decimal places
  var average = total / grades.length;
  var finalGrade = Math.round(average * 100) / 100;

  // 75 and above passes, below 75 fails
  var status = finalGrade >= PASSING_GRADE ? "PASSED" : "FAILED";

  return { status: status, finalGrade: finalGrade };
}
