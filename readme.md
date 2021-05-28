Please run the following commands before starting the application:
composer update

Command for getting SHA:
symfony console repo:last-commit-sha --repo=owner/repo --branch=master --service=github

Command options:
--repo          [REQUIRED]      Enter the repository name. Example: owner/repository-name
--service       [OPTIONAL]      Enter the repository service name. Allow: github
--branch        [OPTIONAL]      Enter the branch name
