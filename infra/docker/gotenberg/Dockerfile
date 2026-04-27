FROM gotenberg/gotenberg:8

USER root

# Install fonts for RTF document rendering:
#   ttf-mscorefonts-installer — actual MS core fonts (Arial, Times New Roman,
#     Courier New, Georgia, Verdana, etc.). Does NOT include Calibri or Cambria.
#   fonts-crosextra-carlito   — metric-compatible substitute for Calibri
#   fonts-crosextra-caladea   — metric-compatible substitute for Cambria
#   fonts-liberation*         — fallback metric-compatible substitutes
#   fonts-urw-base35          — URW++ free clone of the Adobe PostScript
#                               Base35 set (provides Nimbus Mono PS, used
#                               via courierps.conf to satisfy "CourierPS"
#                               in disc-printing RTF templates).
#   fonts-noto-*              — broad Unicode coverage for other docstore templates
# hadolint ignore=DL3008,DL4006
RUN echo "ttf-mscorefonts-installer msttcorefonts/accepted-mscorefonts-eula select true" \
    | debconf-set-selections \
  && apt-get update && apt-get install -y --no-install-recommends \
    ttf-mscorefonts-installer \
    fonts-liberation \
    fonts-crosextra-carlito \
    fonts-crosextra-caladea \
    fonts-urw-base35 \
    fonts-noto-core \
    fonts-noto-extra \
    fontconfig \
  && rm -rf /var/lib/apt/lists/*

# Install Liberation Sans Narrow (not available as a Debian package in bookworm;
# required by document templates). SIL Open Font License.
COPY LiberationSansNarrow-*.ttf /usr/local/share/fonts/

# Install barcode font
COPY 3OF9.TTF /usr/local/share/fonts/3OF9.TTF

# CourierPS -> Nimbus Mono PS alias (see courierps.conf).
COPY courierps.conf /etc/fonts/conf.d/99-courierps.conf

# Rebuild font cache
RUN fc-cache -fv

USER gotenberg
