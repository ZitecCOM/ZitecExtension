Feature: Example of using the POST file upload test
  Please note that this test is very dependant on the project it is used on. CSRF token is handled but there are other ways of protecting the form submission
  This implementation is not final and there are still issues with it. It needs to be tested in some live situations.

  Scenario: Simple example of using POST file upload test - the following test will not work run out of the box because the example site does not have and file requirements
    Given I setup the file uploading test with the following components:
      | allowed_extensions | jpg, gif, png |
      | test_type          | post          |
      | verifications      | mime          |
    And I prepare the post requests with the following parameters:
      | endpoint          | http://cgi-lib.berkeley.edu/ex/fup.cgi |
      | file_upload_field | upfile                                 |
      | note              | Gicu_1                                 |
    And I test the file upload for the given test parameters:
      | success | Gicu_1                           |
      | failure | Request to receive too much data |