EXTRA_DIST =
CLEANFILES =

@distribution@

do_subst = sed \
	-e 's,[@]prefix[@],@prefix@,g' \
	-e 's,[@]phpdir[@],$(phpdir),g' \
	-e 's,[@]localstatedir[@],$(localstatedir),g'

noinst_HEADERS = @PACKAGE@.spec

WSTOPLEVEL_FILES = www/favicon.ico

wstopleveldir = $(wsdocrootdir)
wstoplevel_DATA = $(WSTOPLEVEL_FILES)

WSADMIN_FILES = www/admin/index.php \
	www/admin/logout.php \
	www/admin/style.css 

wsadmindir = $(wsdocrootdir)/admin
wsadmin_DATA = $(WSADMIN_FILES)

WSADDRESSBOOK_FILES = www/admin/addressbook/addr.php \
	www/admin/addressbook/index.php

wsaddressbookdir   = $(wsadmindir)/addressbook
wsaddressbook_DATA = $(WSADDRESSBOOK_FILES)

WSADMINISTRATOR_FILES = www/admin/administrator/admin.php \
	www/admin/administrator/index.php

wsadminstratordir = $(wsadmindir)/administrator
wsadminstrator_DATA = $(WSADMINISTRATOR_FILES)

WSDISTRIBUTIONLIST_FILES = www/admin/distributionlist/index.php \
	www/admin/distributionlist/list.php

wsdistributionlistdir = $(wsadmindir)/distributionlist
wsdistributionlist_DATA = $(WSDISTRIBUTIONLIST_FILES)

WSKOLAB_FILES = www/admin/kolab/erfrakon.php \
	www/admin/kolab/index.php \
	www/admin/kolab/versions.php \
	www/admin/kolab/intevation.php \
	www/admin/kolab/codefusion.php \
	www/admin/kolab/kdab.php \
	www/admin/kolab/kde.php

wskolabdir = $(wsadmindir)/kolab
wskolab_DATA = $(WSKOLAB_FILES)

WSMAINTAINER_FILES = www/admin/maintainer/maintainer.php \
	www/admin/maintainer/index.php

wsmaintainerdir = $(wsadmindir)/maintainer
wsmaintainer_DATA = $(WSMAINTAINER_FILES)

WSPICS_FILES = www/admin/pics/erfrakon.png \
	www/admin/pics/codefusion.png \
	www/admin/pics/intevation_logo.png \
	www/admin/pics/kdab.png \
	www/admin/pics/kde.jpg \
	www/admin/pics/kolab_logo.png

wspicsdir = $(wsadmindir)/pics
wspics_DATA = $(WSPICS_FILES)

WSSERVICE_FILES = www/admin/service/index.php

wsservicedir = $(wsadmindir)/service
wsservice_DATA = $(WSSERVICE_FILES)

WSsharedfolder_FILES = www/admin/sharedfolder/sf.php \
	www/admin/sharedfolder/index.php

wssharedfolderdir = $(wsadmindir)/sharedfolder
wssharedfolder_DATA = $(WSsharedfolder_FILES)

WSUSER_FILES = www/admin/user/forward.php \
	www/admin/user/index.php \
	www/admin/user/user.php \
	www/admin/user/vacation.php \
	www/admin/user/deliver.php

wsuserdir = $(wsadmindir)/user
wsuser_DATA = $(WSUSER_FILES)

phpadmindir = $(phpdir)/admin

php/admin/include/config.php: Makefile php/admin/include/config.php.in
	$(mkinstalldirs) php/admin/include
	$(do_subst) <$(srcdir)/php/admin/include/config.php.in >$@

CLEANFILES += php/admin/include/config.php
EXTRA_DIST += php/admin/include/config.php.in

PHP_INCLUDES = php/admin/include/config.php \
	php/admin/include/menu.php \
	php/admin/include/debug.php \
	php/admin/include/form.class.php \
	php/admin/include/auth.class.php \
	php/admin/include/ldap.class.php \
	php/admin/include/sieveutils.class.php \
	php/admin/include/mysmarty.php \
	php/admin/include/headers.php \
	php/admin/include/authenticate.php \
	php/admin/include/locale.php \
	php/admin/include/Sieve.php

phpincludesdir = $(phpadmindir)/include
phpincludes_DATA = $(PHP_INCLUDES)

PHP_TEMPLATES = php/admin/templates/maintainerdeleted.tpl \
	php/admin/templates/kde.tpl \
	php/admin/templates/addrdeleted.tpl \
	php/admin/templates/userdeleted.tpl \
	php/admin/templates/page.tpl \
	php/admin/templates/addresslist.tpl \
	php/admin/templates/sflistall.tpl \
	php/admin/templates/erfrakon.tpl \
	php/admin/templates/codefusion.tpl \
	php/admin/templates/adminlistall.tpl \
	php/admin/templates/login.tpl \
	php/admin/templates/maintainerlistall.tpl \
	php/admin/templates/kolab.tpl \
	php/admin/templates/versions.tpl \
	php/admin/templates/addrlistall.tpl \
	php/admin/templates/forward.tpl \
	php/admin/templates/deliver.tpl \
	php/admin/templates/distlistall.tpl \
	php/admin/templates/kdab.tpl \
	php/admin/templates/userlistall.tpl \
	php/admin/templates/service.tpl \
	php/admin/templates/welcome.tpl \
	php/admin/templates/systemaliasnagscreen.tpl \
	php/admin/templates/intevation.tpl \
	php/admin/templates/vacation.tpl \
	php/admin/templates/createuser.tpl \
	php/admin/templates/formcontainer.tpl \
	php/admin/templates/admindeleted.tpl

phptemplatesdir = $(phpadmindir)/templates
phptemplates_DATA = $(PHP_TEMPLATES)


### i18n stuff
# gettextize didn't make much sense for a php project
# so we install the .mo files "manually"
# PENDING(steffen): Improve this

MSGFMT=msgfmt

.po.mo:
	$(MSGFMT) -o $@ $<


PHP_LOCALE_DE_PO = php/admin/locale/de/LC_MESSAGES/messages.po
PHP_LOCALE_DE_MO = php/admin/locale/de/LC_MESSAGES/messages.mo
PHP_LOCALE_FR_PO = php/admin/locale/fr/LC_MESSAGES/messages.po
PHP_LOCALE_FR_MO = php/admin/locale/fr/LC_MESSAGES/messages.mo
PHP_LOCALE_NL_PO = php/admin/locale/nl/LC_MESSAGES/messages.po
PHP_LOCALE_NL_MO = php/admin/locale/nl/LC_MESSAGES/messages.mo

localedir = $(phpadmindir)/locale

phplocalededir = $(localedir)/de/LC_MESSAGES
phplocalefrdir = $(localedir)/fr/LC_MESSAGES
phplocalenldir = $(localedir)/nl/LC_MESSAGES

phplocalede_DATA = $(PHP_LOCALE_DE_MO)
phplocalefr_DATA = $(PHP_LOCALE_FR_MO)
phplocalenl_DATA = $(PHP_LOCALE_NL_MO)


EXTRA_DIST += $(WSTOPLEVEL_FILES) \
	$(WSADMIN_FILES) \
	$(WSADDRESSBOOK_FILES) \
	$(WSADMINISTRATOR_FILES) \
	$(WSDISTRIBUTIONLIST_FILES) \
	$(WSKOLAB_FILES) \
	$(WSMAINTAINER_FILES) \
	$(WSPICS_FILES) \
	$(WSSERVICE_FILES) \
	$(WSsharedfolder_FILES) \
	$(WSUSER_FILES) \
	$(PHP_INCLUDES) \
	$(PHP_TEMPLATES) \
	$(PHP_LOCALE_DE_PO) \
	$(PHP_LOCALE_FR_PO) \
	$(PHP_LOCALE_NL_PO)

install-data-hook:
	$(mkinstalldirs) $(DESTDIR)$(phpadmindir)/templates_c
	test -z "$(webserver_usr)" || chown $(webserver_usr) $(DESTDIR)$(phpadmindir)/templates_c
