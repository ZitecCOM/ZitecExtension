Feature: Example of using the POST file upload test
  Please note that this test is very dependant on the project it is used on. CSRF token is handled but there are other ways of protecting the form submission
  This implementation is not final and there are still issues with it. It needs to be tested in some live situations.

  Scenario: More complex example using the dev environment of Regista which also shows the use of the CSRF token detection
    Given I setup the file uploading test with the following components:
      | allowed_extensions | doc, docx |
      | test_type          | post      |
      | csrf               | true      |
    And I prepare the post requests with the following parameters:
      | endpoint                           | https://regista.dev.zitec.ro/documents/save-entry |
      | document_add[RegistrationID]       | nextId                                            |
      | file_upload_field                  | uploadDocument                                    |
      | document_add[OtherDetails]         | Test                                              |
      | document_add[Projects][]           | 1035                                              |
      | document_add[SolveTermOptions]     | 0                                                 |
      | document_add[Pages]                | 1                                                 |
      | document_add[SolveDate]            | 30                                                |
      | document_add[InternalSolveDate]    | 30                                                |
      | MAX_FILE_SIZE                      | 2097152                                           |
      | document_add[ManualRegistrationID] |                                                   |
      | document_add[ExternalId]           |                                                   |
      | document_add[ExternalDate]         |                                                   |
      | document_add[SourceID]             |                                                   |
      | document_add[SourceAddress]        |                                                   |
      | document_add[RecipientID]          |                                                   |
      | document_add[RecipientAddress]     |                                                   |
      | document_add[ExitDate]             |                                                   |
      | document_add[FolderName]           |                                                   |
      | document_add[FolderID]             |                                                   |
      | document_add[SourceType]           |                                                   |
      | document_add[FileDetails]          |                                                   |
      | document_add[entity_id]            |                                                   |
      | document_add[ZFACTION]             | add                                               |
    And I make a POST request to "https://regista.dev.zitec.ro/authentication/login/" with:
      | email    | ionut.voda@zitec.ro |
      | password | prod 1234           |
    And I make a POST request to "https://regista.dev.zitec.ro/counters/change-instance-default-counter" with:
      | counterId | 569 |
    And I am on "https://regista.dev.zitec.ro/documents/add-entry"
    And I process the page to find the CSRF token with the key "document_add[csrf]"
    Then I test the file upload for the given test parameters:
      | success | Documentul a fost salvat cu succes |
      | failure | Acceptam doar fișiere de tipul:    |