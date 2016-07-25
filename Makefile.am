# website files
wwwdir = $(pkgdatadir)/www
www_imgdir = $(pkgdatadir)/www/img
dist_www_DATA = www/index.php www/view.php www/graph.php \
www/device_interface.php
dist_www_img_DATA = www/img/trafficlight_off.svg \
www/img/trafficlight_green.svg www/img/trafficlight_yellow.svg \
www/img/trafficlight_red.svg

# jpgraph php library
jpgraph_archive = www/libs/jpgraph.tar.gz

EXTRA_DIST = $(jpgraph_archive) apache/iaq-server.conf \
apache/001-iaq-server.conf

dist_doc_DATA = README.md COPYING

# copy jpgraph manually
install-data-local:
	$(MKDIR_P) $(DESTDIR)$(wwwdir)/libs; \
	cd $(DESTDIR)$(wwwdir)/libs; \
	$(TAR) -t -f $(abs_srcdir)/$(jpgraph_archive) > uninstall_manifest.txt; \
	$(TAR) -x --no-same-owner -f $(abs_srcdir)/$(jpgraph_archive); \
	<uninstall_manifest.txt sed -e 's/^\|$$/"/g' | xargs chmod a=rX,u+w;

uninstall-local:
	cd $(DESTDIR)$(wwwdir)/libs; \
	<uninstall_manifest.txt sed -e 's/ /\\ /g' | xargs rm -f -R; \
	rm -f uninstall_manifest.txt;
