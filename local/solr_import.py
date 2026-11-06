#!/usr/bin/env python3
import argparse
import requests
from pathlib import Path
from lxml import etree

def wrap_fullrecord_in_cdata(xml_bytes: bytes) -> bytes:
    """Wrap fullrecord field content in CDATA, fallback to escaping."""

    parser = etree.XMLParser(remove_blank_text=False)
    root = etree.fromstring(xml_bytes, parser=parser)

    for field in root.findall(".//field[@name='fullrecord']"):

        # vollständigen inneren XML-Inhalt extrahieren
        inner_xml = "".join(
            etree.tostring(child, encoding="unicode")
            for child in field
        ).strip()

        if not inner_xml:
            continue

        # Kinder entfernen, aber das Attribut 'name' behalten
        for child in list(field):
            field.remove(child)

        # CDATA nicht möglich → escapen
        if "]]>" in inner_xml:
            from html import escape
            field.text = escape(inner_xml)
        else:
            field.text = etree.CDATA(inner_xml)

    return etree.tostring(root, encoding="utf-8")



def clear_solr_core(core: str, solr_url: str | None):
    """Delete all documents from the specified Solr core."""
    if solr_url is None:
        solr_url = f"http://localhost:8983/solr/{core}/update"

    delete_xml = b"<delete><query>*:*</query></delete>"

    print(f"Clearing Solr core via: {solr_url}")

    response = requests.post(
        solr_url,
        data=delete_xml,
        headers={"Content-Type": "application/xml"},
        params={"commit": "true"},
        timeout=30,
    )

    try:
        response.raise_for_status()
        print("✓ Solr core successfully cleared")
    except requests.HTTPError:
        print("❌ Error while clearing Solr core")
        print(response.text)
        raise


def import_xml_to_solr(data_path: Path, core: str, solr_url: str | None, delete_after: bool):
    """Import all XML files into Solr."""
    if solr_url is None:
        solr_url = f"http://localhost:8983/solr/{core}/update"

    headers = {"Content-Type": "application/xml"}

    xml_files = list(data_path.glob("**/*.xml"))
    if not xml_files:
        print(f"No XML files found in {data_path}.")
        return

    print(f"Importing {len(xml_files)} files into {solr_url} ...")

    for xml_file in xml_files:
        

        with xml_file.open("rb") as f:
            raw_xml = f.read()

        xml_data = wrap_fullrecord_in_cdata(raw_xml)

        response = requests.post(
            solr_url,
            data=xml_data,
            headers=headers,
            params={"commit": "true"},
            timeout=30,
        )

        try:
            response.raise_for_status()
            print(f"✓ Success: {xml_file}")

            if delete_after:
                try:
                    xml_file.unlink()
                    print(f"🗑️  Deleted file: {xml_file}")
                except OSError as e:
                    print(f"⚠️  Could not delete file: {xml_file} – {e}")

        except requests.HTTPError:
            print(f"❌ Error with file: {xml_file}")
            print(response.text)
            continue


def main():
    parser = argparse.ArgumentParser(
        description="Imports XML files from a folder into a Solr core."
    )

    parser.add_argument(
        "folder",
        type=Path,
        help="Folder containing the XML files",
    )

    parser.add_argument(
        "core",
        type=str,
        help="Name of the Solr core",
    )

    parser.add_argument(
        "--solr-url",
        type=str,
        default=None,
        help="Optional Solr update URL, e.g. http://localhost:8983/solr/mycore/update",
    )

    parser.add_argument(
        "--delete",
        action="store_true",
        help="Delete XML files after successful import",
    )

    parser.add_argument(
        "--clear",
        action="store_true",
        help="Clear the Solr core before importing data",
    )

    args = parser.parse_args()

    if args.clear:
        clear_solr_core(args.core, args.solr_url)

    import_xml_to_solr(args.folder, args.core, args.solr_url, args.delete)


if __name__ == "__main__":
    main()

