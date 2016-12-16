Feature: Example of using the POST file upload test
  Please note that this test is very dependant on the project it is used on. CSRF token is handled but there are other ways of protecting the form submission
  This implementation is not final and there are still issues with it. It needs to be tested in some live situations.

  Scenario: Another simple example using another test site
    Given I setup the file uploading test with the following components:
      | allowed_extensions | jpg,gif,png,doc,pdf |
      | test_type          | post                |
      | verifications      | large               |
    And I prepare the post requests with the following parameters:
      | endpoint          | http://mime.ritey.com/ |
      | file_upload_field | file1                  |
    And I test the file upload for the given test parameters:
      | success | The MIME type for your file |
      | failure | The MIME type for your file |