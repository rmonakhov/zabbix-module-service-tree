# zabbix-module-service-tree
Written according to Zabbix official documentation [Modules](https://www.zabbix.com/documentation/current/en/devel/modules/file_structure)

A Zabbix module to show services as a tree under Monitoring -> Services tree menu item in Zabbix.


# How to use
1) Create a folder in your Zabbix server modules folder (by default /usr/share/zabbix/) and copy contents of this repository into folder `zabbix-module-service-tree`.
2) Go to Administration -> General -> Modules click Scan directory and enable the module. You should get new 'Services tree' menu item under Monitoring.
