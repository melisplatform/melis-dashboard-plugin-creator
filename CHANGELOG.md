# Changelog

## v6.0.5 - 2026-10-06
### Dependencies & build
* Rebuilt the asset bundle

## v6.0.4 - 2026-09-25
### Security
* **security:** declare the tool key on this module's controllers (audit item 7.0)

## v6.0.3 - 2026-08-20
### Fixed
* **dashboard-plugin-creator-react:** use the code-xml </> icon for the "New" toggle
* **i18n:** drop the duplicated _steps key introduced by the melis-react merge
### Changed
* Tix 7557
### Docs
* **melisai:** React back-office AI documentation for MelisDashboardPluginCreator

## v6.0.2 - 2026-08-10
### Added
* **composer:** add docs link and authors block, swap zf2 keyword for laminas, bump php/melis-tool-creator constraints

## v6.0.1 - 2026-08-10
* Maintenance release.

## v6.0.0 - 2026-08-10
### Security
* **security:** add SECURITY.md (private vulnerability reporting policy)
* Fix audit findings
### Added
* **config:** publish the SVG path of every tab icon offered by the wizard
* **marketplace:** React back-office screenshots (/react)
* **react:** ship the React brick for the Dashboard Plugin Creator (step 3 wording: Dashboard title -> Plugin title)
### Fixed
* **i18n:** translate the wizard 'Steps' zone label in the English file
* **dashboard-plugin-creator:** fall back to a filled language for menu/dashboard titles
* **security:** harden legacy file/dir creation & output escaping
* **react:** make the post-generation platform reload timer-chain proof
* **react:** use the tab icons picked in the wizard
### Dependencies & build
* **deps:** require melisplatform/melis-core ^6.0
* Local WIP snapshot before reconcile (20260806-114605)
* **sync:** align melis-react branch with parent deliverable

## v5.3.2 - 2024-11-20
### Changed
* Delete temp-thumbnail dir inside module

## v5.3.1 - 2024-09-26
### Changed
* Dashboard menu cache deletion
* Module name case function update

## v5.3.0 - 2024-09-25
### Changed
* Interface roles translations
* Bs5 tab
* Update jQuery 3.7.1 migration

## v5.2.0 - 2024-06-06
* Maintenance release.

## v5.1.0 - 2024-02-13
* Maintenance release.

## v5.0.0 - 2022-06-22
### Changed
* Changed deprecated ArraySerializable to ArraySerializableHydrator and updated other functions affected by php 8
### Dependencies & build
* Removed laminas paginator and updated php version
