# cPanel release cleanup and rollback

LogicStrand uses the public `A1-CM/.github` toolkit at tag `v1.0.9`. Both caller workflows pin their reusable workflow and toolkit checkout to that tag.

## Deployment behavior

- The app remains in `<CPANEL_HOME>/<project>-app/releases/<release>`. `public_html` contains the managed Laravel entry point and public assets. Shared storage and `deployment-history.json` remain under `<project>-app/shared`.
- After activation, the runner checks the configured `health_path` (default `/up`) three times. A failure invokes the restore endpoint, which puts the previous entry point, routing rules, and overwritten public files back. Database migrations are **not** reversed.
- After a healthy activation, the toolkit records the new active release and at most seven previous releases. It deletes only matching project staging ZIPs in `CPANEL_HOME` and strict release-ID directories beyond that set. Failed releases and ZIPs remain until a later healthy deployment.
- The first updated run imports the current managed release and up to seven complete older releases. Incomplete releases are excluded from rollback and pruned after the healthy deployment.
- The deployer asks cPanel for other domain document roots and rejects public files that would enter a nested addon domain root. Rollback reuses this protection. Domains that share exactly the same document root intentionally share its public entry point; review those in cPanel before deployment.

## Manual rollback

Run the repository's **Roll back LogicStrand on cPanel** action and enter `steps_back` from `1` to `7`. `1` restores the most recently active earlier release. A completed rollback places the release it replaced first in history, so another `steps_back=1` can return to it. The action checks `/up` and restores the prior live files if that check fails.

The reusable rollback workflow needs `CPANEL_HOST`, `CPANEL_USERNAME`, `CPANEL_HOME`, and `APP_URL` variables, plus the `CPANEL_API_TOKEN` secret, with the same access as deployment. `CPANEL_HEALTH_PATH` is optional. It uses the same concurrency group as deployment.

## Updating the toolkit

For future updates, test the shared toolkit, publish a new immutable tag in `A1-CM/.github`, then update both `uses:` and `toolkit_ref` in each LogicStrand caller workflow to the same tag. Run a staging cPanel deployment and rollback before publishing a release when a staging account is available.

Deployment history is private and file permissions are restricted. A missing or corrupt history file stops rollback rather than guessing a release. The cPanel API token and generated application credentials are never printed in workflow output.

The deployment workflow requires the organization or repository `MAIL_HOST` Actions secret. Grant each calling repository access to the organization secret. The caller already uses `secrets: inherit`; no separate mail-host entry is needed in its YAML.
