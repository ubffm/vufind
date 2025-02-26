#!/bin/bash

# Verzeichnis, das gesichert werden soll
SOURCE_DIR="local/"
# Zielverzeichnis für das Backup
BACKUP_DIR="vufind_local_backups/"
# Zeitstempel für die Backup-Datei
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")
# Name der Backup-Datei
BACKUP_FILE="$BACKUP_DIR/backup_$TIMESTAMP.tar.gz"

# Prüfen, ob Quell- und Zielverzeichnis angegeben wurden
if [ -z "$SOURCE_DIR" ] || [ -z "$BACKUP_DIR" ]; then
    echo "Verwendung: $0 <Quellverzeichnis> <Zielverzeichnis>"
    exit 1
fi

# Prüfen, ob das Quellverzeichnis existiert
if [ ! -d "$SOURCE_DIR" ]; then
    echo "Fehler: Das Quellverzeichnis existiert nicht!"
    exit 1
fi

# Sicherstellen, dass das Zielverzeichnis existiert
mkdir -p "$BACKUP_DIR"

# Backup erstellen
 tar -czf "$BACKUP_FILE" --exclude="local/cache/*" "$SOURCE_DIR"

# Erfolgsmeldung ausgeben
echo "Backup erfolgreich erstellt: $BACKUP_FILE"

