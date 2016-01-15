<?php
/**
 * Zc的系统配置常量，为了避免框架配置和应用配置冲突，所以框架配置都在这里定义成常量，并且加zc前缀
 * 
 * @author tangjianhui
 *
 */
class ZcConfigConst {

	const DirFsRoot = 'zc.dir.fs.root';
	const DirFsApp = 'zc.dir.fs.app';
	const DirWsApp = 'zc.dir.ws.app';
	const DirFsConf = 'zc.dir.fs.conf';
	const DirFsLanguages = 'zc.dir.fs.languages';
	
	const DirFsViewsLayout = 'zc.dir.fs.views.layout';
	const DirFsViewsPage = 'zc.dir.fs.views.page';
	const DirFsViewsWidget = 'zc.dir.fs.views.widget';
	const DirWsViewsStatic = 'zc.dir.ws.views.static';
	
	const DirFsLibs = 'zc.dir.fs.libs';
	const DirFsLibsController = 'zc.dir.fs.libs.controller';
	const DirFsLibsWidget = 'zc.dir.fs.libs.widget';
	
	const LanguageDefault = 'zc.language.default';
	const LanguageCurrent = 'zc.language.current';
	
	const AutoloadDirsFs = 'zc.autoload.dirs.fs';
	const AutoloadDirsWs = 'zc.autoload.dirs.ws';
	const AutoloadClassFileMapping = 'zc.autoload.class.file.mapping';
	const AutoloadIncludeFiles = 'zc.autoload.include.files';
	
	const Filters = 'zc.filters';
	
	const UrlHandler = 'zc.url.handler';
	
	const DefaultRoute = 'zc.default.route';
	const DefaultTimezone = 'zc.default.timezone';
	const DefaultOutputEncoding = 'zc.output.encoding';
	const CleanQuotes = 'zc.clean.quotes';
	const Pagination = 'zc.pagination';
	
	const MonitorAutostart = 'zc.monitor.autostart';
	const MonitorHandler = 'zc.monitor.handler';
	const MonitorDbServer = 'zc.monitor.db.server';
	const MonitorDbUsername = 'zc.monitor.db.username';
	const MonitorDbPassword = 'zc.monitor.db.password';
	const MonitorDbDatabase = 'zc.monitor.db.database';
	const MonitorExitOnDbError = 'monitor.db.error';
	
	const LogDir = 'zc.log.dir';
	const LogLevel = 'zc.log.level';
	const LogHandlerLogstashredisEnable = 'zc.log.handler.logstashredis.enable';
	const LogHandlerLogstashredisHost = 'zc.log.handler.logstashredis.host';
	const LogHandlerLogstashredisPort = 'zc.log.handler.logstashredis.port';
	const LogHandlerLogstashredisKey = 'zc.log.handler.logstashredis.key';
	
	const DbConfig = 'zc.db.config';
	const DbHostname = 'zc.db.hostname';
	const DbUsername = 'zc.db.username';
	const DbPassword = 'zc.db.password';
	const DbDatabase = 'zc.db.database';
}