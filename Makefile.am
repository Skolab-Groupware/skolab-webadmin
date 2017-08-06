NULL =

SUBDIRS = data lib test htdocs

EXTRA_DIST =

MAINTAINERCLEANFILES =					\
    $(srcdir)/autom4te.cache/*				\
    $(srcdir)/build-aux/*               		\
    $(srcdir)/data/Makefile.in				\
    $(srcdir)/data/locale/Makefile.in			\
    $(srcdir)/data/templates/Makefile.in		\
    $(srcdir)/lib/skolab/admin/Makefile.in		\
    $(srcdir)/lib/Makefile.in				\
    $(srcdir)/test/Makefile.in				\
    $(srcdir)/www/Makefile.in				\
    $(srcdir)/www/admin/Makefile.in			\
    $(srcdir)/www/admin/addressbook/Makefile.in		\
    $(srcdir)/www/admin/administrator/Makefile.in	\
    $(srcdir)/www/admin/distributionlist/Makefile.in	\
    $(srcdir)/www/admin/domainmaintainer/Makefile.in	\
    $(srcdir)/www/admin/images/Makefile.in		\
    $(srcdir)/www/admin/kolab/Makefile.in		\
    $(srcdir)/www/admin/maintainer/Makefile.in		\
    $(srcdir)/www/admin/settings/Makefile.in		\
    $(srcdir)/www/admin/sharedfolder/Makefile.in	\
    $(srcdir)/www/admin/user/Makefile.in		\
    $(srcdir)/aclocal.m4				\
    $(srcdir)/config.h.in				\
    $(srcdir)/config.h.in~				\
    $(srcdir)/configure					\
    $(NULL)

DISTCLEANFILES=$(MAINTAINERCLEANFILES)

install-data-hook:
	$(mkinstalldirs) $(DESTDIR)$(smarty_compiledir)
	ln -s ${sysconfdir}/skolab/webadmin/session_vars.php $(DESTDIR)${phplib_dir}/skolab/admin/include/session_vars.php
	ln -s ${sysconfdir}/skolab/webadmin/config.php       $(DESTDIR)${phplib_dir}/skolab/admin/include/config.php
# 	test -z "$(webserver_user)" || chown $(webserver_user) $(DESTDIR)$(phpkolabdir)/templates_c
