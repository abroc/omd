include ../../Makefile.omd

NAME = apache-omd
VERSION = 1.0
DIR = $(NAME)-$(VERSION)
FCGI_FILE="mod_fcgid-2.3.9"
MOD_RPAF_VERSION="0.8"

APACHE_MODULES=modrpaf
APACHE_MODULES_INSTALL=modrpaf_install
APACHE_FCGID_PATH=$(MODULE_DIR)/$(APACHE_FCGID_MODULE)
CENTOS_WORKAROUND=0
INSTALL_FCGID=0
REPLACE_PHP_FCGI_BIN=$(PHP_FCGI_BIN)

# Try to find the apxs binary
APXS=/usr/sbin/apxs
ifeq ("$(wildcard $(APXS))","")
    APXS=apxs
endif
ifneq ("$(wildcard /usr/sbin/apxs2)","")
    APXS=/usr/sbin/apxs2
endif

VERSIONLT7 := $(shell expr $(DISTRO_VERSION) \<= 7)
ifeq ($(DISTRO_NAME),CENTOS)
  ifeq ($(VERSIONLT7), 1)
    CENTOS_WORKAROUND=1
  endif
endif
ifeq ($(DISTRO_NAME),REDHAT)
  ifeq ($(VERSIONLT7), 1)
    CENTOS_WORKAROUND=1
  endif
endif
ifneq (,$(findstring 11SP,$(DISTRO_VERSION)))
  INSTALL_FCGID=1
endif

ifeq ($(CENTOS_WORKAROUND), 1)
  # workaround for centos/rh which ignores "php-cgi -d settings"
  REPLACE_PHP_FCGI_BIN=$(PHP_FCGI_BIN) -c $(OMD_ROOT)/etc/apache/php.ini
  INSTALL_FCGID=1
endif

ifeq ($(INSTALL_FCGID), 1)
  APACHE_MODULES+=modfcgid
  APACHE_MODULES_INSTALL+=modfcgid_install
  APACHE_FCGID_PATH=$(OMD_ROOT)/lib/apache/mod_fcgid.so
endif

.PHONY: skel

build: $(APACHE_MODULES)

install: $(APACHE_MODULES_INSTALL)
	# Install software below $(DESTDIR)$(OMD_ROOT)/{bin,lib,share}
	mkdir -p $(DESTDIR)$(OMD_ROOT)/share/omd
	if [ $(APACHE_24_OR_NEWER) -eq 1 ]; then \
		install -m 644 apache24.conf $(DESTDIR)$(OMD_ROOT)/share/omd/apache.conf; \
	else \
		install -m 644 apache.conf $(DESTDIR)$(OMD_ROOT)/share/omd/apache.conf; \
	fi

skel:
	if [ $(CENTOS_WORKAROUND) -eq 1 ]; then \
		cp /etc/php.ini $(SKEL)/etc/apache/php.ini || exit 1; \
		echo -e "\n\n; OMD OMD OMD OMD OMD OMD\n\nmemory_limit=64M\n\n[Session]\nsession.save_path=###ROOT###/tmp/php/session\nupload_tmp_dir=###ROOT###/tmp/php/upload\nsoap.wsdl_cache_dir=###ROOT###/tmp/php/wsdl-cache\n" >> $(SKEL)/etc/apache/php.ini; \
	fi
	
	# install a custom apache configuration for apache 2.4 or later
	if [ $(APACHE_24_OR_NEWER) -eq 1 ]; then \
	    mv $(SKEL)/etc/apache/apache24.conf $(SKEL)/etc/apache/apache.conf ; \
	else \
	    rm $(SKEL)/etc/apache/apache24.conf ; \
	fi

	# Replace macros
	sed -i -e 's|###APACHE_MODULE_DIR###|$(MODULE_DIR)|g' \
	       $(SKEL)/etc/apache/conf.d/01_rpaf.conf \
	       $(SKEL)/etc/apache/apache.conf \
	       $(SKEL)/etc/apache/apache-ssl.conf

	# SLES12 has apache 2.4 but does not have the prefork module to be loaded,
	# so remove this load line for this distro from the apache config
	if [ ! -e $(MODULE_DIR)/mod_mpm_prefork.so ]; then \
	    sed -i '/^LoadModule mpm_prefork_module/d' $(SKEL)/etc/apache/apache.conf ; \
	fi

	sed -i -e 's|###APACHE_FCGID_MODULE###|$(APACHE_FCGID_PATH)|g' \
	       $(SKEL)/etc/apache/conf.d/02_fcgid.conf

	sed -i 's|###APACHE_BIN###|$(APACHE_BIN)|g' $(SKEL)/etc/init.d/apache
	sed -i 's|###PHP_FCGI_BIN###|$(PHP_FCGI_BIN)|g' $(SKEL)/etc/init.d/apache
	sed -i 's|###PHP_FCGI_BIN###|$(REPLACE_PHP_FCGI_BIN)|g' $(SKEL)/etc/apache/php-wrapper
	# Create working directories
	mkdir -p $(SKEL)/var/log/apache
	mkdir -p $(SKEL)/var/www
	mkdir -p $(SKEL)/tmp/apache/run
	mkdir -p $(SKEL)/tmp/apache/fcgid_sock
	mkdir -p $(SKEL)/tmp/php/session
	mkdir -p $(SKEL)/tmp/php/upload
	mkdir -p $(SKEL)/tmp/php/wsdl-cache

clean:
	# Remove files created by build/install
	rm -rf $(FCGI_FILE)
	rm -rf mod_rpaf-$(MOD_RPAF_VERSION)

modfcgid:
	tar zxf $(FCGI_FILE).tar.gz
	cd $(FCGI_FILE) && patch -p1 < ../patches/0001-fcgid_proc_unix.c.patch
	cd $(FCGI_FILE) && patch -p1 < ../patches/0002-fcgid_pm_unix.c.patch
	cd $(FCGI_FILE) && APXS=$(APXS) ./configure.apxs
	CPATH="/usr/include/apache2-worker" $(MAKE) -C $(FCGI_FILE)

modfcgid_install:
	mkdir -p $(DESTDIR)$(OMD_ROOT)/lib/apache
	cp $(FCGI_FILE)/modules/fcgid/.libs/mod_fcgid.so $(DESTDIR)$(OMD_ROOT)/lib/apache/

modrpaf:
	tar zxf mod_rpaf-$(MOD_RPAF_VERSION).tar.gz
	make -C mod_rpaf-$(MOD_RPAF_VERSION)

modrpaf_install:
	mkdir -p $(DESTDIR)$(OMD_ROOT)/lib/apache
	cp mod_rpaf-$(MOD_RPAF_VERSION)/.libs/mod_rpaf.so $(DESTDIR)$(OMD_ROOT)/lib/apache/
