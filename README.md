# PUQ_WHMCS-Public-Synology

Module for the WHMCS system.
For manage Synology users as a product.

Functions:

- Auto create and deploy produkt
- Only Synology API using
- Multilanguage
- Using disk space statistics (UsageUpdate) peer user

Admin area:

- Create users
- Suspend users
- Terminate users
- Unsuspend users
- Change the Synology users password
- API connection status
- Synology users disk status

Client area:

- Change the user password
- Synology user disk status
---------------------------------------------------------------
Testing:

WHMCS: 8.1.0

Synology: DSM 6.2.4-25556

--------------------------------------------------------------
### WHMCS part setup guide
1. ```git clone https://github.com/PUQ-sp-z-o-o/PUQ_WHMCS-Public-Synology.git```
2. Copy "puqPublicSynology" to "WHMCS_WEB_DIR/modules/servers/"

2. Create new server Synology in WHMCS (System Settings->Products/Services->Servers)  
- Hostname: Synology cloud server DNS
- IP Address: IP adres Synology server
- Module: PUQ Public Synology
- Username: Synology admin user
- Password: Synology admin user password
- Set and used ONLY https port

3. Create a new Products/Services
- Module Settings/Module Name: PUQ Public Synology
- Group: Name of group on Synology server for this produkt
