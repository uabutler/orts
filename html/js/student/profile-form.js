/*
 * INPUT VALIDATION
 */
function validateBannerId() { return setError(validateRegex("banner_id", /^001\d{6}$/), "banner_id"); }
function validateGradYear() { return setError(validateRegex("year", /^20[2-9]\d$/), "year"); }
function validateFirstName() { return setError(validateNotEmpty("first_name"), "first_name"); }
function validateLastName() { return setError(validateNotEmpty("last_name"), "last_name"); }
function validateGradMonth() { return setError(validateNotEmpty("grad_month"), "grad_month"); }
function validateStanding() { return setError(validateNotEmpty("standing"), "standing"); }

