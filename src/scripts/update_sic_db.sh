#!/bin/bash

cd /tmp
# Desinstalamos
WINEDEBUG=fixme-all msiexec /x /tmp/sic/Sic_Tributacion.msi /quiet /passive /qn /Li /tmp/sic.log

# Eliminamos residuos
rm -rf /tmp/sic/ /tmp/sic_installed/ /tmp/.rebasedata-php-client-cache

# Verificamos si ya el archivo comprimido fue descargado
if [[ ! -f 'SIC.zip' ]]; then
    wget -q http://www.hacienda.go.cr/declaraweb/software/declar@7/soportes/sic/SIC.zip
fi

# Extraer
7za e SIC.zip -oc:/tmp/sic -r

cd /tmp/sic
# Instalamos SIC
WINEDEBUG=fixme-all msiexec /i /tmp/sic/Sic_Tributacion.msi TARGETDIR="/tmp/sic_installed" /quiet /passive /qn /Li /tmp/sic.log

# Install msi file with msiexec unattended in Ubuntu Server. (Stackover flow)