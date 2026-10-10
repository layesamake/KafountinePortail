"""Build a byte-stable installable ZIP for CK Admin communal."""
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

ROOT = Path(__file__).resolve().parent.parent
PACKAGE = ROOT / "exports" / "ck-admin-communal"
OUTPUT = ROOT / "exports" / "ck-admin-communal.zip"
EPOCH = (2020, 1, 1, 0, 0, 0)

files = sorted(path for path in PACKAGE.rglob("*") if path.is_file())
with ZipFile(OUTPUT, "w", compression=ZIP_DEFLATED, compresslevel=9) as archive:
    for path in files:
        relative = path.relative_to(PACKAGE).as_posix()
        info = ZipInfo(f"ck-admin-communal/{relative}", EPOCH)
        info.compress_type = ZIP_DEFLATED
        info.create_system = 0
        info.external_attr = 0o100644 << 16
        archive.writestr(info, path.read_bytes())

print(f"Created {OUTPUT} ({len(files)} files)")
