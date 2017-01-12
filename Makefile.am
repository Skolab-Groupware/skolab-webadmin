EXTRA_DIST =
CLEANFILES =

@distribution@
@common@

# With or Without openpkg (w_or_wo_openpkg) definition
if OPENPKG
	w_or_wo_openpkg = sed -e 's/^@OPENPKG@//;/^@WITHOUT_OPENPKG@/d'
else
	w_or_wo_openpkg = sed -e 's/^@WITHOUT_OPENPKG@//;/^@OPENPKG@/d'
endif

distclean-local:
	rm -rf $(srcdir)/autom4te*.cache

noinst_HEADERS = @PACKAGE@.spec

WSTOPLEVEL_FILES = www/favicon.png

wstopleveldir = $(webserver_document_root)$(webserver_web_prefix)
dist_wstoplevel_DATA = $(WSTOPLEVEL_FILES)

WSKOLAB_FILES = www/admin/index.php \
	www/admin/logout.php \
	www/admin/print.css  \
	www/admin/style.css 

wskolabdir = $(webserver_document_root)$(kolab_wui)
wskolab_DATA = $(WSKOLAB_FILES)
EXTRA_DIST += www/admin/style.css \
	www/admin/print.css

WSADDRESSBOOK_FILES = www/admin/addressbook/addr.php \
	www/admin/addressbook/index.php

wsaddressbookdir   = $(wskolabdir)/addressbook
wsaddressbook_DATA = $(WSADDRESSBOOK_FILES)

WSADMINISTRATOR_FILES = www/admin/administrator/admin.php \
	www/admin/administrator/index.php

wsadminstratordir = $(wskolabdir)/administrator
wsadminstrator_DATA = $(WSADMINISTRATOR_FILES)

WSDISTRIBUTIONLIST_FILES = www/admin/distributionlist/index.php \
	www/admin/distributionlist/list.php

wsdistributionlistdir = $(wskolabdir)/distributionlist
wsdistributionlist_DATA = $(WSDISTRIBUTIONLIST_FILES)

WSABOUT_FILES = www/admin/kolab/erfrakon.php \
	www/admin/kolab/index.php \
	www/admin/kolab/intevation.php \
	www/admin/kolab/codefusion.php \
	www/admin/kolab/kdab.php \
	www/admin/kolab/kde.php \
	www/admin/kolab/versions.php

wsaboutdir = $(wskolabdir)/kolab
wsabout_DATA = $(WSABOUT_FILES)

WSMAINTAINER_FILES = www/admin/maintainer/maintainer.php \
	www/admin/maintainer/index.php

wsmaintainerdir = $(wskolabdir)/maintainer
wsmaintainer_DATA = $(WSMAINTAINER_FILES)

WSDOMAINMAINTAINER_FILES = www/admin/domainmaintainer/domainmaintainer.php \
	www/admin/domainmaintainer/index.php

wsdomainmaintainerdir = $(wskolabdir)/domainmaintainer
wsdomainmaintainer_DATA = $(WSDOMAINMAINTAINER_FILES)

WSPICS_FILES = www/admin/pics/erfrakon.png \
	www/admin/pics/codefusion.png \
	www/admin/pics/intevation_logo.png \
	www/admin/pics/kdab.png \
	www/admin/pics/kde.png \
	www/admin/pics/kolab_logo.png

wspicsdir = $(wskolabdir)/pics
dist_wspics_DATA = $(WSPICS_FILES)

wssettingsdir = $(wskolabdir)/settings
wssettings_DATA = www/admin/settings/index.php

WSSHAREDFOLDER_FILES = www/admin/sharedfolder/sf.php \
	www/admin/sharedfolder/index.php

wssharedfolderdir = $(wskolabdir)/sharedfolder
wssharedfolder_DATA = $(WSSHAREDFOLDER_FILES)

WSUSER_FILES = www/admin/user/forward.php \
	www/admin/user/index.php \
	www/admin/user/user.php \
	www/admin/user/vacation.php \
	www/admin/user/deliver.php

wsuserdir = $(wskolabdir)/user
wsuser_DATA = $(WSUSER_FILES)

phpkolabdir = $(phplibdir2)/$(kolab_php_module_prefix)admin

php_includes_configdir = $(phpkolabdir)/include
php_includes_config_DATA = php/admin/include/config.php

PHP_INCLUDES = php/admin/include/menu.php \
	php/admin/include/debug.php \
	php/admin/include/form.class.php \
	php/admin/include/sieveutils.class.php \
	php/admin/include/authenticate.php \
	php/admin/include/locale.php \
	php/admin/include/Sieve.php \
	php/admin/include/passwd.php

phpincludesdir = $(phpkolabdir)/include
phpincludes_DATA = $(PHP_INCLUDES) \
	php/admin/include/auth.class.php \
	php/admin/include/ldap.class.php \
	php/admin/include/headers.php \
	php/admin/include/mysmarty.php 
EXTRA_DIST += $(PHP_INCLUDES)

PHP_TEMPLATES = php/admin/templates/maintainerdeleted.tpl \
	php/admin/templates/kde.tpl \
	php/admin/templates/addrdeleted.tpl \
	php/admin/templates/userdeleted.tpl \
	php/admin/templates/sfdeleted.tpl \
	php/admin/templates/distlistdeleted.tpl \
	php/admin/templates/addresslist.tpl \
	php/admin/templates/sflistall.tpl \
	php/admin/templates/erfrakon.tpl \
	php/admin/templates/codefusion.tpl \
	php/admin/templates/adminlistall.tpl \
	php/admin/templates/login.tpl \
	php/admin/templates/maintainerlistall.tpl \
	php/admin/templates/domainmaintainerlistall.tpl \
	php/admin/templates/kolab.tpl \
	php/admin/templates/addrlistall.tpl \
	php/admin/templates/forward.tpl \
	php/admin/templates/deliver.tpl \
	php/admin/templates/distlistall.tpl \
	php/admin/templates/kdab.tpl \
	php/admin/templates/userlistall.tpl \
	php/admin/templates/userlisterror.tpl \
	php/admin/templates/settings.tpl \
	php/admin/templates/welcome.tpl \
	php/admin/templates/systemaliasnagscreen.tpl \
	php/admin/templates/intevation.tpl \
	php/admin/templates/vacation.tpl \
	php/admin/templates/createuser.tpl \
	php/admin/templates/formcontainer.tpl \
	php/admin/templates/admindeleted.tpl

phptemplatesdir = $(phpkolabdir)/templates
phptemplates_DATA = $(PHP_TEMPLATES) \
	php/admin/templates/page.tpl \
	php/admin/templates/versions.tpl
EXTRA_DIST += $(PHP_TEMPLATES)


### i18n stuff
# gettextize didn't make much sense for a php project
# so we install the .mo files "manually"
# PENDING(steffen): Improve this

MSGFMT=msgfmt

.po.mo: $<
# Without $(mkinstalldirs) `echo ... | sed ...` a 'make distcheck' would
# fail as the target directory (e.g. php/admin/locale/de/LC_MESSAGES)
# does not yes exist at build time.  Use the `echo ... | sed ...` construction
# as something smarter, like basename, results in configure to complain
# about not portable filenames.
	@$(mkinstalldirs) `echo "$@" | sed 's|messages\.mo||'`
	$(MSGFMT) -o $@ $<

CLEANFILES += php/admin/locale/*/LC_MESSAGES/messages.mo


PHP_LOCALE_DE_PO = php/admin/locale/de/LC_MESSAGES/messages.po
PHP_LOCALE_DE_MO = php/admin/locale/de/LC_MESSAGES/messages.mo
PHP_LOCALE_FR_PO = php/admin/locale/fr/LC_MESSAGES/messages.po
PHP_LOCALE_FR_MO = php/admin/locale/fr/LC_MESSAGES/messages.mo
PHP_LOCALE_NL_PO = php/admin/locale/nl/LC_MESSAGES/messages.po
PHP_LOCALE_NL_MO = php/admin/locale/nl/LC_MESSAGES/messages.mo
PHP_LOCALE_IT_PO = php/admin/locale/it/LC_MESSAGES/messages.po
PHP_LOCALE_IT_MO = php/admin/locale/it/LC_MESSAGES/messages.mo
PHP_LOCALE_ES_PO = php/admin/locale/es/LC_MESSAGES/messages.po
PHP_LOCALE_ES_MO = php/admin/locale/es/LC_MESSAGES/messages.mo

localedir = $(phpkolabdir)/locale

phplocalededir = $(localedir)/de/LC_MESSAGES
phplocalefrdir = $(localedir)/fr/LC_MESSAGES
phplocalenldir = $(localedir)/nl/LC_MESSAGES
phplocaleitdir = $(localedir)/it/LC_MESSAGES
phplocaleesdir = $(localedir)/es/LC_MESSAGES

phplocalede_DATA = $(PHP_LOCALE_DE_MO)
phplocalefr_DATA = $(PHP_LOCALE_FR_MO)
phplocalenl_DATA = $(PHP_LOCALE_NL_MO)
phplocaleit_DATA = $(PHP_LOCALE_IT_MO)
phplocalees_DATA = $(PHP_LOCALE_ES_MO)

EXTRA_DIST += $(PHP_LOCALE_DE_PO) \
	$(PHP_LOCALE_FR_PO) \
	$(PHP_LOCALE_NL_PO) \
	$(PHP_LOCALE_IT_PO) \
	$(PHP_LOCALE_ES_PO)

kwadocdir = $(pkgdocdir)/@PACKAGE@
dist_kwadoc_DATA = ChangeLog \
        COPYING \
        NEWS \
        AUTHORS

EXTRA_DIST += dist_conf/common \
	dist_conf/debian \
	dist_conf/kolab \
	dist_conf/mandriva \
	dist_conf/suse

www/admin/addressbook/addr.php: www/admin/addressbook/addr.php.in
	@$(mkinstalldirs) www/admin/addressbook
	$(do_subst) <$(srcdir)/www/admin/addressbook/addr.php.in >$@
CLEANFILES += www/admin/addressbook/addr.php
EXTRA_DIST += www/admin/addressbook/addr.php.in

www/admin/addressbook/index.php: www/admin/addressbook/index.php.in
	@$(mkinstalldirs) www/admin/addressbook
	$(do_subst) <$(srcdir)/www/admin/addressbook/index.php.in >$@
CLEANFILES += www/admin/addressbook/index.php
EXTRA_DIST += www/admin/addressbook/index.php.in

www/admin/administrator/admin.php: www/admin/administrator/admin.php.in
	@$(mkinstalldirs) www/admin/administrator
	$(do_subst) <$(srcdir)/www/admin/administrator/admin.php.in >$@
CLEANFILES += www/admin/administrator/admin.php
EXTRA_DIST += www/admin/administrator/admin.php.in

www/admin/administrator/index.php: www/admin/administrator/index.php.in
	@$(mkinstalldirs) www/admin/administrator
	$(do_subst) <$(srcdir)/www/admin/administrator/index.php.in >$@
CLEANFILES += www/admin/administrator/index.php
EXTRA_DIST += www/admin/administrator/index.php.in

www/admin/distributionlist/index.php: www/admin/distributionlist/index.php.in
	@$(mkinstalldirs) www/admin/distributionlist
	$(do_subst) <$(srcdir)/www/admin/distributionlist/index.php.in >$@
CLEANFILES += www/admin/distributionlist/index.php
EXTRA_DIST += www/admin/distributionlist/index.php.in

www/admin/distributionlist/list.php: www/admin/distributionlist/list.php.in
	@$(mkinstalldirs) www/admin/distributionlist
	$(do_subst) <$(srcdir)/www/admin/distributionlist/list.php.in >$@
CLEANFILES += www/admin/distributionlist/list.php
EXTRA_DIST += www/admin/distributionlist/list.php.in

www/admin/domainmaintainer/domainmaintainer.php: www/admin/domainmaintainer/domainmaintainer.php.in
	@$(mkinstalldirs) www/admin/domainmaintainer
	$(do_subst) <$(srcdir)/www/admin/domainmaintainer/domainmaintainer.php.in >$@
CLEANFILES += www/admin/domainmaintainer/domainmaintainer.php
EXTRA_DIST += www/admin/domainmaintainer/domainmaintainer.php.in

www/admin/domainmaintainer/index.php: www/admin/domainmaintainer/index.php.in
	@$(mkinstalldirs) www/admin/domainmaintainer
	$(do_subst) <$(srcdir)/www/admin/domainmaintainer/index.php.in >$@
CLEANFILES += www/admin/domainmaintainer/index.php
EXTRA_DIST += www/admin/domainmaintainer/index.php.in

www/admin/index.php: www/admin/index.php.in
	@$(mkinstalldirs) www/admin
	$(do_subst) <$(srcdir)/www/admin/index.php.in >$@
CLEANFILES += www/admin/index.php
EXTRA_DIST += www/admin/index.php.in

www/admin/kolab/codefusion.php: www/admin/kolab/codefusion.php.in
	@$(mkinstalldirs) www/admin/kolab
	$(do_subst) <$(srcdir)/www/admin/kolab/codefusion.php.in >$@
CLEANFILES += www/admin/kolab/codefusion.php
EXTRA_DIST += www/admin/kolab/codefusion.php.in

www/admin/kolab/erfrakon.php: www/admin/kolab/erfrakon.php.in
	@$(mkinstalldirs) www/admin/kolab
	$(do_subst) <$(srcdir)/www/admin/kolab/erfrakon.php.in >$@
CLEANFILES += www/admin/kolab/erfrakon.php
EXTRA_DIST += www/admin/kolab/erfrakon.php.in

www/admin/kolab/index.php: www/admin/kolab/index.php.in
	@$(mkinstalldirs) www/admin/kolab
	$(do_subst) <$(srcdir)/www/admin/kolab/index.php.in >$@
CLEANFILES += www/admin/kolab/index.php
EXTRA_DIST += www/admin/kolab/index.php.in

www/admin/kolab/intevation.php: www/admin/kolab/intevation.php.in
	@$(mkinstalldirs) www/admin/kolab
	$(do_subst) <$(srcdir)/www/admin/kolab/intevation.php.in >$@
CLEANFILES += www/admin/kolab/intevation.php
EXTRA_DIST += www/admin/kolab/intevation.php.in

www/admin/kolab/kdab.php: www/admin/kolab/kdab.php.in
	@$(mkinstalldirs) www/admin/kolab
	$(do_subst) <$(srcdir)/www/admin/kolab/kdab.php.in >$@
CLEANFILES += www/admin/kolab/kdab.php
EXTRA_DIST += www/admin/kolab/kdab.php.in

www/admin/kolab/kde.php: www/admin/kolab/kde.php.in
	@$(mkinstalldirs) www/admin/kolab
	$(do_subst) <$(srcdir)/www/admin/kolab/kde.php.in >$@
CLEANFILES += www/admin/kolab/kde.php
EXTRA_DIST += www/admin/kolab/kde.php.in

www/admin/maintainer/index.php: www/admin/maintainer/index.php.in
	@$(mkinstalldirs) www/admin/maintainer
	$(do_subst) <$(srcdir)/www/admin/maintainer/index.php.in >$@
CLEANFILES += www/admin/maintainer/index.php
EXTRA_DIST += www/admin/maintainer/index.php.in

www/admin/maintainer/maintainer.php: www/admin/maintainer/maintainer.php.in
	@$(mkinstalldirs) www/admin/maintainer
	$(do_subst) <$(srcdir)/www/admin/maintainer/maintainer.php.in >$@
CLEANFILES += www/admin/maintainer/maintainer.php
EXTRA_DIST += www/admin/maintainer/maintainer.php.in

www/admin/settings/index.php: www/admin/settings/index.php.in
	@$(mkinstalldirs) www/admin/settings
	$(do_subst) <$(srcdir)/www/admin/settings/index.php.in >$@
CLEANFILES += www/admin/settings/index.php
EXTRA_DIST += www/admin/settings/index.php.in

www/admin/sharedfolder/index.php: www/admin/sharedfolder/index.php.in
	@$(mkinstalldirs) www/admin/sharedfolder
	$(do_subst) <$(srcdir)/www/admin/sharedfolder/index.php.in >$@
CLEANFILES += www/admin/sharedfolder/index.php
EXTRA_DIST += www/admin/sharedfolder/index.php.in

www/admin/sharedfolder/sf.php: www/admin/sharedfolder/sf.php.in
	@$(mkinstalldirs) www/admin/sharedfolder
	$(do_subst) <$(srcdir)/www/admin/sharedfolder/sf.php.in >$@
CLEANFILES += www/admin/sharedfolder/sf.php
EXTRA_DIST += www/admin/sharedfolder/sf.php.in

www/admin/user/deliver.php: www/admin/user/deliver.php.in
	@$(mkinstalldirs) www/admin/user
	$(do_subst) <$(srcdir)/www/admin/user/deliver.php.in >$@
CLEANFILES += www/admin/user/deliver.php
EXTRA_DIST += www/admin/user/deliver.php.in

www/admin/user/forward.php: www/admin/user/forward.php.in
	@$(mkinstalldirs) www/admin/user
	$(do_subst) <$(srcdir)/www/admin/user/forward.php.in >$@
CLEANFILES += www/admin/user/forward.php
EXTRA_DIST += www/admin/user/forward.php.in

www/admin/user/index.php: www/admin/user/index.php.in
	@$(mkinstalldirs) www/admin/user
	$(do_subst) <$(srcdir)/www/admin/user/index.php.in >$@
CLEANFILES += www/admin/user/index.php
EXTRA_DIST += www/admin/user/index.php.in

www/admin/user/vacation.php: www/admin/user/vacation.php.in
	@$(mkinstalldirs) www/admin/user
	$(do_subst) <$(srcdir)/www/admin/user/vacation.php.in >$@
CLEANFILES += www/admin/user/vacation.php
EXTRA_DIST += www/admin/user/vacation.php.in
###

php/admin/include/auth.class.php: php/admin/include/auth.class.php.in
	@$(mkinstalldirs) php/admin/include
	$(do_subst) <$(srcdir)/php/admin/include/auth.class.php.in >$@
CLEANFILES += php/admin/include/auth.class.php
EXTRA_DIST += php/admin/include/auth.class.php.in

php/admin/include/config.php: php/admin/include/config.php.in
	$(mkinstalldirs) php/admin/include
	$(do_subst) <$(srcdir)/php/admin/include/config.php.in >$@
CLEANFILES += php/admin/include/config.php
EXTRA_DIST += php/admin/include/config.php.in

php/admin/include/headers.php: php/admin/include/headers.php.in
	@$(mkinstalldirs) php/admin/include
	$(do_subst) <$(srcdir)/php/admin/include/headers.php.in >$@
CLEANFILES += php/admin/include/headers.php
EXTRA_DIST += php/admin/include/headers.php.in

php/admin/include/mysmarty.php: php/admin/include/mysmarty.php.in
	@$(mkinstalldirs) php/admin/include
	$(do_subst) <$(srcdir)/php/admin/include/mysmarty.php.in >$@
CLEANFILES += php/admin/include/mysmarty.php
EXTRA_DIST += php/admin/include/mysmarty.php.in

php/admin/include/ldap.class.php: php/admin/include/ldap.class.php.in
	@$(mkinstalldirs) php/admin/include
	$(do_subst) <$(srcdir)/php/admin/include/ldap.class.php.in >$@
CLEANFILES += php/admin/include/ldap.class.php
EXTRA_DIST += php/admin/include/ldap.class.php.in

php/admin/templates/page.tpl: php/admin/templates/page.tpl.in
	@$(mkinstalldirs) php/admin/templates
	$(do_subst) <$(srcdir)/php/admin/templates/page.tpl.in >$@
CLEANFILES += php/admin/templates/page.tpl
EXTRA_DIST += php/admin/templates/page.tpl.in

php/admin/templates/versions.tpl: php/admin/templates/versions.tpl.in
	@$(mkinstalldirs) php/admin/templates
	$(w_or_wo_openpkg) <$(srcdir)/php/admin/templates/versions.tpl.in >$@
CLEANFILES += php/admin/templates/versions.tpl
EXTRA_DIST += php/admin/templates/versions.tpl.in

www/admin/logout.php: www/admin/logout.php.in
	@$(mkinstalldirs) www/admin
	$(do_subst) <$(srcdir)/www/admin/logout.php.in >$@
CLEANFILES += www/admin/logout.php
EXTRA_DIST += www/admin/logout.php.in

www/admin/user/user.php: www/admin/user/user.php.in
	@$(mkinstalldirs) www/admin/user
	$(do_subst) <$(srcdir)/www/admin/user/user.php.in >$@
CLEANFILES += www/admin/user/user.php
EXTRA_DIST += www/admin/user/user.php.in

www/admin/kolab/versions.php: www/admin/kolab/versions.php.in
	@$(mkinstalldirs) www/admin/kolab
	$(do_subst) <$(srcdir)/www/admin/kolab/versions.php.in >$@
CLEANFILES += www/admin/kolab/versions.php
EXTRA_DIST += www/admin/kolab/versions.php.in


install-data-hook:
	$(mkinstalldirs) $(DESTDIR)$(smarty_compiledir)
# 	test -z "$(webserver_usr)" || chown $(webserver_usr) $(DESTDIR)$(phpkolabdir)/templates_c
