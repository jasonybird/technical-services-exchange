#!/usr/bin/env python3
from datetime import datetime
from pathlib import Path


CONF = Path("/etc/nginx/sites-available/christit.com")
MARKER = "    client_max_body_size 12G;\n"
STAMP = datetime.now().strftime("%Y%m%d-%H%M%S")

BLOCK = r'''

    # Technical Services Exchange Laravel test app.
    # App root: /sites/provider-exchange; only /public is exposed.
    location = /tse {
        return 301 /tse/;
    }

    location = /tse/ {
        return 302 /tse/jobs;
    }

    location = /tse/index.php {
        try_files /__missing_tse_index @tse_front_controller;
    }

    location @tse_front_controller {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
        fastcgi_intercept_errors on;
        fastcgi_param SCRIPT_FILENAME /sites/provider-exchange/public/index.php;
        fastcgi_param DOCUMENT_ROOT /sites/provider-exchange/public;
        fastcgi_param SCRIPT_NAME /tse/index.php;
        fastcgi_param HTTPS on;
    }

    location ^~ /tse/ {
        alias /sites/provider-exchange/public/;
        try_files $uri $uri/ @tse_front_controller;
    }
'''


def main() -> None:
    text = CONF.read_text()

    if "Technical Services Exchange Laravel test app" in text:
        start = text.index("    # Technical Services Exchange Laravel test app.")
        end = text.index("\n    location / {", start)
        updated = text[:start] + BLOCK.lstrip("\n") + text[end:]

        if updated == text:
            print("NOOP: /tse block already present")
            return

        backup = CONF.with_name(f"{CONF.name}.provider-exchange-tse-{STAMP}.bak")
        backup.write_text(text)
        CONF.write_text(updated)
        print(f"UPDATED: {CONF}")
        print(f"BACKUP: {backup}")
        return

    if MARKER not in text:
        raise SystemExit(f"Could not find insertion marker in {CONF}")

    backup = CONF.with_name(f"{CONF.name}.provider-exchange-tse-{STAMP}.bak")
    backup.write_text(text)
    CONF.write_text(text.replace(MARKER, MARKER + BLOCK, 1))
    print(f"UPDATED: {CONF}")
    print(f"BACKUP: {backup}")


if __name__ == "__main__":
    main()
