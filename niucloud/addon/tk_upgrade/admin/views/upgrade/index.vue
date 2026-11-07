<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50">
    <div class="mx-auto px-4 py-6">
      <!-- 页面头部 - 简洁协调布局 -->
      <div class="mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <div class="flex items-center justify-between">
            <!-- 左侧信息区 -->
            <div class="flex items-center space-x-6">
              <!-- 授权信息 -->
              <div v-if="auth" class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                  <el-icon class="text-white text-sm">
                    <Lock />
                  </el-icon>
                </div>
                <div>
                  <div class="text-sm font-semibold text-gray-900">{{ auth.domain || '离线版本' }}</div>
                  <div class="text-xs text-gray-500">{{ auth.domain ? '更新到期:' + auth?.over_time : '离线版本' }}</div>

                </div>
              </div>

              <!-- 分割线 -->
              <div class="w-px h-8 bg-gray-300"></div>

              <!-- 框架版本 -->
              <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                  <el-icon class="text-white text-sm">
                    <Box />
                  </el-icon>
                </div>
                <div>
                  <div class="text-sm font-semibold text-gray-900">框架版本: v{{ auth?.frame_version }}</div>
                  <div v-if="auth?.frame_update == 1" class="text-xs text-green-600">最新版本: v{{ auth?.frame_version_cloud
                  }}</div>
                  <div v-else class="text-xs text-gray-500">已是最新版本</div>
                </div>
              </div>
              <div class="w-px h-8 bg-gray-300"></div>
              <!-- 框架更新按钮 -->
              <el-button v-if="auth?.frame_update == 1" type="primary" @click="showFrameworkUpdateDialog"
                class="font-medium">
                <el-icon class="mr-1">
                  <Upload />
                </el-icon>
                更新框架
              </el-button>
              <el-button v-if="auth?.frame_update == 0 && auth?.frame_version_cloud != ''" type="plain"
                @click="showFrameworkUpdateDialog" class="font-medium">
                <el-icon class="mr-1">
                  <Tools />
                </el-icon>
                一键修复
              </el-button>
              <el-button v-if="auth?.frame_update == 0 && auth?.frame_version_cloud != ''" type="plain"
                @click="showAdminDialog = true" class="font-medium">
                <el-icon class="mr-1">
                  <Monitor />
                </el-icon>
                更新前端
              </el-button>
              <el-button v-if="auth?.frame_version_cloud != ''" type="plain" @click="frameDialogFn" class="font-medium">
                <el-icon class="mr-1">
                  <Document />
                </el-icon>
                更新记录
              </el-button>
            </div>

            <!-- 右侧操作区 -->
            <div class="flex items-center space-x-4">


              <!-- 应用管理 -->
              <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                  <el-icon class="text-white text-sm">
                    <Download />
                  </el-icon>
                </div>
                <div>
                  <div class="text-sm font-semibold text-gray-900">应用管理</div>
                  <div class="flex items-center space-x-3 text-xs">
                    <span class="text-green-600">已安装: {{ getInstalledCount() }}</span>
                    <span class="text-orange-600">可更新: {{ getUpdatableCount() }}</span>
                    <span class="text-blue-600">未安装: {{ getInstallableCount() }}</span>
                  </div>
                </div>
              </div>

              <!-- 批量操作按钮 -->
              <div class="flex space-x-3">
                <el-button type="primary" @click="handleInstallAll()" :loading="batchLoading.install" size="default"
                  :disabled="!getInstallableCount()" class="font-semibold px-5">
                  <el-icon class="mr-2 text-base">
                    <Download />
                  </el-icon>
                  安装({{ getInstallableCount() }})
                </el-button>

                <el-button type="warning" @click="handleUpdateAll()" :loading="batchLoading.update" size="default"
                  :disabled="!getUpdatableCount()" class="font-semibold px-5">
                  <el-icon class="mr-2 text-base">
                    <Refresh />
                  </el-icon>
                  更新({{ getUpdatableCount() }})
                </el-button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要内容区域 -->
      <el-card v-loading="loading" :element-loading-text="loadingText" class="!border-none shadow-sm" shadow="never">

        <div class="flex container flex-wrap gap-4">
          <div v-for="(item, index) in addonList" :key="index"
            class="w-full sm:w-[280px] bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col h-full">
            <!-- 卡片头部 -->
            <div class="flex items-center justify-between px-3 py-2 bg-gray-50 border-b border-gray-100">
              <div class="flex items-center space-x-2 flex-1 min-w-0">
                <span class="text-sm font-semibold text-gray-800 truncate">{{ item.name }}</span>
                <span v-if="item.local_version"
                  class="text-xs text-gray-500 bg-white px-2 py-1 rounded-full flex-shrink-0">
                  v{{ item.local_version }}
                </span>
              </div>
              <el-tag :type="item.is_install ? (item.is_update ? 'warning' : 'success') : 'info'" size="small"
                effect="plain" class="flex-shrink-0">
                {{ item.is_install ? (item.is_update ? (item.is_update == 0 ? '无更新' : '可更新') : '已安装') : '未安装' }}
              </el-tag>
            </div>

            <!-- 卡片内容 -->
            <div class="p-3 flex-1 flex flex-col">
              <div class="flex items-start space-x-3 mb-3">
                <!-- 左侧图标 -->
                <div
                  class="w-12 h-12 flex-shrink-0 bg-gradient-to-br from-blue-50 to-indigo-100 rounded-lg overflow-hidden border border-gray-200">
                  <el-image :src="item.icon" class="w-full h-full object-cover" fit="cover" :preview-disabled="true">
                    <template #error>
                      <div
                        class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                        <el-icon class="text-gray-400 text-lg">
                          <Picture />
                        </el-icon>
                      </div>
                    </template>
                  </el-image>
                </div>

                <!-- 右侧信息 -->
                <div class="flex-1 min-w-0">
                  <h4 class="text-sm font-medium text-gray-800 mb-1 line-clamp-1">{{ item.name }}</h4>
                  <p v-if="item.description" class="text-xs text-gray-500 line-clamp-2 leading-relaxed mb-2">
                    {{ item.description }}
                  </p>

                  <!-- 版本信息 -->
                  <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-1">
                      <span class="text-gray-500">版本:</span>
                      <span class="text-gray-700 font-medium">{{ item.local_version || '未安装' }}</span>
                      <span v-if="item.is_update && item.version" class="text-orange-600">
                        → v{{ item.version }}
                      </span>
                    </div>
                    <el-button @click="openVersonDialogFn(item)" type="text" size="small"
                      class="text-xs text-blue-600 hover:text-blue-800 p-0 h-auto">
                      更新记录
                    </el-button>
                  </div>
                </div>
              </div>
            </div>

            <!-- 卡片底部操作 -->
            <div class="px-3 py-2 bg-gray-50 border-t border-gray-100 mt-auto">
              <!-- 有更新状态 - 水平布局显示更新和卸载按钮 -->
              <div v-if="item.is_update == 1" class="flex items-center space-x-2">
                <el-button v-if="item.is_over == 0" type="warning" size="small" class="flex-1 relative"
                  @click="installAddonFn(item)" :loading="installLoading[item.key]"
                  :disabled="installLoading[item.key]">
                  <template v-if="!installLoading[item.key]">
                    <el-icon class="mr-1">
                      <Upload />
                    </el-icon>
                    立即更新
                  </template>
                  <template v-else>
                    <el-icon class="mr-1 animate-spin">
                      <Refresh />
                    </el-icon>
                    更新中...
                  </template>
                </el-button>
                <el-button v-if="item.is_over == 1" size="small" class="flex-1 relative">
                  {{ item.over_time }}
                  已到期
                </el-button>
                <el-button type="danger" size="small" @click="uninstallAddonFn(item)"
                  :loading="uninstallLoading[item.key]" :disabled="uninstallLoading[item.key]" plain
                  class="flex-shrink-0">
                  <template v-if="!uninstallLoading[item.key]">
                    <el-icon class="mr-1">
                      <Delete />
                    </el-icon>
                    卸载
                  </template>
                  <template v-else>
                    <el-icon class="mr-1 animate-spin">
                      <Refresh />
                    </el-icon>
                    卸载中
                  </template>
                </el-button>
              </div>

              <!-- 无更新状态 -->
              <div v-else>
                <el-button v-if="item.is_install == 0 && item.version != null" type="primary" size="small"
                  class="w-full relative" @click="installAddonFn(item)" :loading="installLoading[item.key]"
                  :disabled="installLoading[item.key]">
                  <template v-if="!installLoading[item.key]">
                    <el-icon class="mr-1">
                      <Download />
                    </el-icon>
                    立即安装
                  </template>
                  <template v-else>
                    <el-icon class="mr-1 animate-spin">
                      <Refresh />
                    </el-icon>
                    安装中...
                  </template>
                </el-button>

                <!-- 已安装状态 - 水平布局显示状态和卸载按钮 -->
                <div v-else-if="item.is_install == 1" class="flex items-center space-x-2">
                  <el-button type="success" size="small" class="flex-1" @click="handleUpdateFromDialog(item)">
                    <el-icon class="mr-1">
                      <Check />
                    </el-icon>
                    一键修复
                  </el-button>
                  <el-button type="danger" size="small" @click="uninstallAddonFn(item)"
                    :loading="uninstallLoading[item.key]" :disabled="uninstallLoading[item.key]" plain
                    class="flex-shrink-0">
                    <template v-if="!uninstallLoading[item.key]">
                      <el-icon class="mr-1">
                        <Delete />
                      </el-icon>
                      卸载
                    </template>
                    <template v-else>
                      <el-icon class="mr-1 animate-spin">
                        <Refresh />
                      </el-icon>
                      卸载中
                    </template>
                  </el-button>
                </div>

                <el-button v-else-if="item.is_install == 0 && item.version == null" type="info" size="small" disabled
                  class="w-full">
                  <el-icon class="mr-1">
                    <Warning />
                  </el-icon>
                  暂无版本
                </el-button>
              </div>
            </div>
          </div>
        </div>

        <!-- 空状态 -->
        <div v-if="!addonList || addonList.length === 0" class="w-full">
          <div class="text-center py-16">
            <div
              class="w-24 h-24 mx-auto mb-4 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
              <el-icon class="text-4xl text-gray-400">
                <Picture />
              </el-icon>
            </div>
            <h3 class="text-lg font-medium text-gray-600 mb-2">正在获取授权数据</h3>
          </div>
        </div>

      </el-card>
    </div>
  </div>

  <!-- 版本记录弹窗 -->
  <el-dialog v-model="versionDialogVisible" width="650px" top="8vh" @close="handleVersionDialogClose"
    class="version-dialog" :close-on-click-modal="false" :append-to-body="true">
    <template #header="{ close, titleId, titleClass }">
      <div
        class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
            <el-icon class="text-white text-lg">
              <Document />
            </el-icon>
          </div>
          <div>
            <h3 :id="titleId" :class="titleClass" class="text-lg font-semibold text-gray-800">
              {{ currentVersionInfo?.name || '' }}
            </h3>
            <div class="flex items-center space-x-2 text-sm">
              <span class="text-gray-500">当前版本:</span>
              <el-tag size="small" type="info">{{ currentVersionInfo?.local_version || '未安装' }}</el-tag>
              <span v-if="currentVersionInfo?.is_update == 1" class="text-orange-600">
                → 可更新至 v{{ currentVersionInfo?.version }}
              </span>
            </div>
          </div>
        </div>

      </div>
    </template>

    <div class="px-4 py-2">
      <div v-if="versionList && versionList.length > 0" class="space-y-4 max-h-96 overflow-y-auto custom-scrollbar">
        <div v-for="(version, index) in versionList" :key="index"
          class="relative bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200"
          :class="{ 'border-l-4 border-l-green-500': index === 0 }">
          <!-- 版本标签 -->
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-2">
              <el-tag :type="index === 0 ? 'success' : 'info'" size="small" effect="dark" class="font-medium">
                v{{ version.version }}
              </el-tag>
              <span v-if="index === 0" class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-full font-medium">
                最新版本
              </span>
            </div>
            <div class="flex items-center space-x-1 text-gray-500">
              <el-icon class="text-sm">
                <Calendar />
              </el-icon>
              <span class="text-sm">{{ version.create_time }}</span>
            </div>
          </div>

          <!-- 更新内容 -->
          <div class="version-content">
            <div v-if="version.desc" class="text-gray-700 leading-relaxed text-sm"
              v-html="formatVersionContent(version.desc)"></div>
            <div v-else class="text-gray-400 italic text-sm flex items-center justify-center py-4">
              <el-icon class="mr-2">
                <InfoFilled />
              </el-icon>
              暂无更新说明
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-12">
        <el-empty description="暂无版本记录" :image-size="80">
          <template #description>
            <p class="text-gray-500">该应用暂无版本更新记录</p>
          </template>
        </el-empty>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-between items-center px-4 py-3 bg-gray-50 border-t border-gray-100">
        <!-- 版本状态信息 -->
        <div class="flex items-center space-x-2 text-sm">
          <span class="text-gray-600">状态:</span>
          <el-tag v-if="currentVersionInfo?.is_install == 1 && currentVersionInfo?.is_update == 0" type="success"
            size="small">
            已是最新版本
          </el-tag>
          <el-tag v-else-if="currentVersionInfo?.is_install == 1 && currentVersionInfo?.is_update == 1" type="warning"
            size="small">
            有新版本可更新
          </el-tag>
          <el-tag v-else-if="currentVersionInfo?.is_install == 0" type="info" size="small">
            未安装
          </el-tag>
        </div>

        <!-- 操作按钮 -->
        <div class="flex items-center space-x-2">
          <el-button
            v-if="currentVersionInfo?.is_update == 1 && currentVersionInfo?.is_install == 1 && currentVersionInfo?.is_over == 0"
            type="primary" size="default" @click.stop="handleUpdateFromDialog(currentVersionInfo)"
            :loading="updateLoading">
            <el-icon class="mr-1">
              <Upload />
            </el-icon>
            立即更新
          </el-button>

          <el-button v-if="currentVersionInfo?.is_install == 0 && currentVersionInfo?.version" type="success"
            size="default" @click.stop="handleUpdateFromDialog(currentVersionInfo)" :loading="updateLoading">
            <el-icon class="mr-1">
              <Download />
            </el-icon>
            立即安装
          </el-button>

          <el-button @click="handleVersionDialogClose" size="default">
            关闭
          </el-button>
        </div>
      </div>
    </template>
  </el-dialog>


  <!-- 框架更新确认弹窗 -->
  <el-dialog v-model="frameworkUpdateDialog" title="" width="520px" :close-on-click-modal="false"
    :close-on-press-escape="false" class="framework-update-dialog" align-center>
    <!-- 自定义头部 -->
    <template #header>
      <div class="flex items-center justify-between p-6 pb-0">
        <div class="flex items-center space-x-3">
          <div
            class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
            <el-icon class="text-2xl text-white">
              <Refresh />
            </el-icon>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900">框架更新确认</h3>
            <p class="text-sm text-gray-500 mt-1">请仔细阅读以下更新提醒</p>
          </div>
        </div>
        <el-button @click="frameworkUpdateDialog = false" type="text" class="text-gray-400 hover:text-gray-600 p-2"
          size="large">

        </el-button>
      </div>
    </template>

    <div class="px-6 pb-2">
      <!-- 版本信息卡片 -->
      <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 mb-6 border border-blue-100">
        <div class="flex items-center justify-between">
          <div class="text-center flex-1">
            <div class="text-sm text-gray-600 mb-1">当前版本</div>
            <div class="text-lg font-bold text-blue-600 font-mono">v{{ auth?.frame_version }}</div>
          </div>
          <div class="mx-4">
            <el-icon class="text-2xl text-gray-400">
              <Right />
            </el-icon>
          </div>
          <div class="text-center flex-1">
            <div class="text-sm text-gray-600 mb-1">最新版本</div>
            <div class="text-lg font-bold text-green-600 font-mono">v{{ auth?.frame_version_cloud }}</div>
          </div>
        </div>
      </div>

      <!-- 重要提醒 -->
      <div class="bg-gradient-to-r from-amber-50 to-orange-50 border-l-4 border-amber-400 rounded-lg p-4 mb-6">
        <div class="flex items-start space-x-3">
          <div class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
            <el-icon class="text-white text-sm">
              <Warning />
            </el-icon>
          </div>
          <div class="flex-1">
            <h4 class="text-sm font-bold text-amber-800 mb-3">更新前重要提醒</h4>
            <div class="grid grid-cols-1 gap-2">
              <div class="flex items-center space-x-2 text-sm text-amber-700">
                <div class="w-1.5 h-1.5 bg-amber-500 rounded-full"></div>
                <span>请务必备份数据库和重要文件</span>
              </div>
              <div class="flex items-center space-x-2 text-sm text-amber-700">
                <div class="w-1.5 h-1.5 bg-amber-500 rounded-full"></div>
                <span>更新过程中请勿关闭浏览器或刷新页面</span>
              </div>
              <div class="flex items-center space-x-2 text-sm text-amber-700">
                <div class="w-1.5 h-1.5 bg-amber-500 rounded-full"></div>
                <span>建议在业务低峰期进行更新操作</span>
              </div>
              <div class="flex items-center space-x-2 text-sm text-amber-700">
                <div class="w-1.5 h-1.5 bg-amber-500 rounded-full"></div>
                <span>更新完成请重启守护进程</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end space-x-3 px-6 pb-6">
        <el-button @click="frameworkUpdateDialog = false" size="large" class="px-6">
          取消
        </el-button>
        <el-button type="primary" @click="confirmFrameworkUpdate" size="large"
          class="px-6 bg-gradient-to-r from-blue-500 to-indigo-600 border-0 font-semibold">
          <el-icon class="mr-2">
            <Check />
          </el-icon>
          确认更新
        </el-button>
      </div>
    </template>
  </el-dialog>

  <!-- 框架更新进度弹窗 -->
  <el-dialog v-model="frameworkUpdateProgressDialog" title="" width="500px" :close-on-click-modal="false"
    :close-on-press-escape="false" :show-close="false" class="framework-progress-dialog" align-center>
    <div class="text-center p-8">
      <!-- 动画加载器 -->
      <div class="mb-8 relative">
        <div class="relative inline-block">
          <!-- 外圈旋转动画 -->
          <div
            class="w-28 h-28 border-4 border-blue-100 rounded-full animate-spin border-t-blue-500 border-r-indigo-500">
          </div>
          <!-- 中圈脉冲动画 -->
          <div
            class="absolute inset-2 w-24 h-24 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center animate-pulse shadow-lg">
            <el-icon class="text-4xl text-white">
              <Refresh />
            </el-icon>
          </div>
          <!-- 光晕效果 -->
          <div class="absolute inset-0 w-28 h-28 bg-blue-400 rounded-full opacity-20 animate-ping"></div>
        </div>

        <!-- 进度环 -->
        <div class="absolute inset-0 flex items-center justify-center">
          <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="45" stroke="rgba(59, 130, 246, 0.1)" stroke-width="2" fill="none" />
            <circle cx="50" cy="50" r="45" stroke="url(#progressGradient)" stroke-width="2" fill="none"
              stroke-linecap="round" :stroke-dasharray="`${frameworkUpdateProgress * 2.83} 283`"
              class="transition-all duration-500 ease-out" />
            <defs>
              <linearGradient id="progressGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" style="stop-color:#3b82f6" />
                <stop offset="100%" style="stop-color:#6366f1" />
              </linearGradient>
            </defs>
          </svg>
        </div>
      </div>

      <!-- 更新状态 -->
      <div class="mb-6">
        <h3 class="text-2xl font-bold text-gray-900 mb-2">正在更新框架</h3>
        <p class="text-gray-600 text-lg font-medium">{{ frameworkUpdateStatus }}</p>
      </div>


      <!-- 提示信息 -->
      <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-center justify-center space-x-3">
          <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center animate-pulse">
            <el-icon class="text-white">
              <InfoFilled />
            </el-icon>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-blue-800 mb-1">请耐心等待</p>
            <p class="text-xs text-blue-600">更新完成后页面将自动刷新，请勿关闭浏览器</p>
          </div>
        </div>
      </div>
    </div>
  </el-dialog>
  <!-- 前端更新确认弹窗 -->
  <el-dialog v-model="showAdminDialog" title="" width="520px" :close-on-click-modal="false"
    :close-on-press-escape="false" class="framework-update-dialog" align-center>
    <!-- 自定义头部 -->
    <template #header>
      <div class="flex items-center justify-between p-6 pb-0">
        <div class="flex items-center space-x-3">
          <div
            class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
            <el-icon class="text-2xl text-white">
              <Download />
            </el-icon>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900">前端更新确认</h3>
            <p class="text-sm text-gray-500 mt-1">请仔细阅读以下更新提醒</p>
          </div>
        </div>
        <el-button @click="showAdminDialog = false" type="text" class="text-gray-400 hover:text-gray-600 p-2"
          size="large">

        </el-button>
      </div>
    </template>

    <div class="px-6 pb-2">
      <!-- 版本信息卡片 -->
      <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 mb-6 border border-blue-100">
        <div class="flex items-center justify-center">
          <div class="text-center">
            <div class="text-lg font-bold text-blue-600 font-mono">此操作会更新最新的后端管理页面和H5</div>
            <div class="text-xs text-gray-500 mt-1">插件安装升级后可直接拉取</div>
          </div>
        </div>
      </div>

    </div>

    <template #footer>
      <div class="flex justify-end space-x-3 px-6 pb-6">
        <el-button @click="showAdminDialog = false" size="large" class="px-6">
          取消
        </el-button>
        <el-button type="primary" @click="updateAdminFn" size="large"
          class="px-6 bg-gradient-to-r from-blue-500 to-indigo-600 border-0 font-semibold">
          <el-icon class="mr-2">
            <Check />
          </el-icon>
          确认更新
        </el-button>
      </div>
    </template>
  </el-dialog>

  <!-- 前端更新进度弹窗 -->
  <el-dialog v-model="adminUpdateProgressDialog" title="" width="500px" :close-on-click-modal="false"
    :close-on-press-escape="false" :show-close="false" class="framework-progress-dialog" align-center>
    <div class="text-center p-8">
      <!-- 动画加载器 -->
      <div class="mb-8 relative">
        <div class="relative inline-block">
          <!-- 外圈旋转动画 -->
          <div
            class="w-28 h-28 border-4 border-blue-100 rounded-full animate-spin border-t-blue-500 border-r-indigo-500">
          </div>
          <!-- 中圈脉冲动画 -->
          <div
            class="absolute inset-2 w-24 h-24 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center animate-pulse shadow-lg">
            <el-icon class="text-4xl text-white">
              <Download />
            </el-icon>
          </div>
          <!-- 光晕效果 -->
          <div class="absolute inset-0 w-28 h-28 bg-blue-400 rounded-full opacity-20 animate-ping"></div>
        </div>

        <!-- 进度环 -->
        <div class="absolute inset-0 flex items-center justify-center">
          <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="45" stroke="rgba(59, 130, 246, 0.1)" stroke-width="2" fill="none" />
            <circle cx="50" cy="50" r="45" stroke="url(#progressGradient)" stroke-width="2" fill="none"
              stroke-linecap="round" :stroke-dasharray="`${frameworkUpdateProgress * 2.83} 283`"
              class="transition-all duration-500 ease-out" />
            <defs>
              <linearGradient id="progressGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" style="stop-color:#3b82f6" />
                <stop offset="100%" style="stop-color:#6366f1" />
              </linearGradient>
            </defs>
          </svg>
        </div>
      </div>

      <!-- 更新状态 -->
      <div class="mb-6">
        <h3 class="text-2xl font-bold text-gray-900 mb-2">正在更新前端</h3>
        <p class="text-gray-600 text-lg font-medium">{{ adminUpdateStatus }}</p>
      </div>

      <!-- 提示信息 -->
      <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-center justify-center space-x-3">
          <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center animate-pulse">
            <el-icon class="text-white">
              <InfoFilled />
            </el-icon>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-blue-800 mb-1">请耐心等待</p>
            <p class="text-xs text-blue-600">更新完成后页面将自动刷新，请勿关闭浏览器</p>
          </div>
        </div>
      </div>
    </div>
  </el-dialog>
  <el-dialog v-model="frameDialog" width="650px" top="8vh" @close="frameDialog = false" class="version-dialog"
    :close-on-click-modal="false">
    <div>框架更新记录</div>
    <div class="px-4 py-2">
      <div v-if="versionList && versionList.length > 0" class="space-y-4 max-h-96 overflow-y-auto custom-scrollbar">
        <div v-for="(version, index) in versionList" :key="index"
          class="relative bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200"
          :class="{ 'border-l-4 border-l-green-500': index === 0 }">
          <!-- 版本标签 -->
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-2">
              <el-tag :type="index === 0 ? 'success' : 'info'" size="small" effect="dark" class="font-medium">
                v{{ version.version }}
              </el-tag>
              <span v-if="index === 0" class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-full font-medium">
                最新版本
              </span>
            </div>
            <div class="flex items-center space-x-1 text-gray-500">
              <el-icon class="text-sm">
                <Calendar />
              </el-icon>
              <span class="text-sm">{{ version.create_time }}</span>
            </div>
          </div>

          <!-- 更新内容 -->
          <div class="version-content">
            <div v-if="version.desc" class="text-gray-700 leading-relaxed text-sm"
              v-html="formatVersionContent(version.desc)"></div>
            <div v-else class="text-gray-400 italic text-sm flex items-center justify-center py-4">
              <el-icon class="mr-2">
                <InfoFilled />
              </el-icon>
              暂无更新说明
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-12">
        <el-empty description="暂无版本记录" :image-size="80">
          <template #description>
            <p class="text-gray-500">该应用暂无版本更新记录</p>
          </template>
        </el-empty>
      </div>
    </div>

  </el-dialog>

  <!-- 图像预览弹窗 -->
  <el-dialog v-model="imagePreviewVisible" :show-close="false" :close-on-click-modal="true"
    :close-on-press-escape="true" width="90%" top="5vh" class="image-preview-dialog" @close="closeImagePreview">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2 text-white">
          <el-icon class="text-lg">
            <Picture />
          </el-icon>
          <span class="font-medium">图像预览</span>
          <span v-if="previewImageList.length > 1" class="text-sm opacity-80">
            ({{ currentImageIndex + 1 }} / {{ previewImageList.length }})
          </span>
        </div>
        <el-button @click="closeImagePreview" type="text" class="text-white hover:text-gray-300 p-2" size="large">
          <el-icon class="text-xl">
            <Close />
          </el-icon>
        </el-button>
      </div>
    </template>

    <div class="image-preview-container">
      <!-- 主图像显示区域 -->
      <div class="flex items-center justify-center min-h-[60vh]">
        <el-image v-for="(imageUrl, index) in previewImageList" :key="index" v-show="index === currentImageIndex"
          :src="imageUrl" fit="contain" class="max-w-full max-h-[70vh] rounded-lg shadow-2xl" :preview-disabled="true">
          <template #error>
            <div class="flex items-center justify-center w-full h-64 bg-gray-100 rounded-lg">
              <el-icon class="text-4xl text-gray-400">
                <Picture />
              </el-icon>
            </div>
          </template>
        </el-image>
      </div>

      <!-- 导航按钮 -->
      <div v-if="previewImageList.length > 1" class="flex items-center justify-center mt-6 space-x-4">
        <el-button @click="previousImage" :disabled="currentImageIndex === 0" type="primary" circle class="w-12 h-12">
          <el-icon class="text-lg">
            <ArrowLeft />
          </el-icon>
        </el-button>

        <span class="text-white font-medium px-4 py-2 bg-black bg-opacity-30 rounded-full">
          {{ currentImageIndex + 1 }} / {{ previewImageList.length }}
        </span>

        <el-button @click="nextImage" :disabled="currentImageIndex === previewImageList.length - 1" type="primary"
          circle class="w-12 h-12">
          <el-icon class="text-lg">
            <ArrowRight />
          </el-icon>
        </el-button>
      </div>

      <!-- 缩略图导航 -->
      <div v-if="previewImageList.length > 1"
        class="flex items-center justify-center mt-4 space-x-2 overflow-x-auto pb-2">
        <div v-for="(imageUrl, index) in previewImageList" :key="index" @click="currentImageIndex = index"
          class="flex-shrink-0 w-16 h-16 cursor-pointer rounded-lg overflow-hidden border-2 transition-all duration-200"
          :class="index === currentImageIndex ? 'border-blue-500 shadow-lg' : 'border-gray-300 hover:border-gray-400'">
          <el-image :src="imageUrl" fit="cover" class="w-full h-full" :preview-disabled="true">
            <template #error>
              <div class="flex items-center justify-center w-full h-full bg-gray-100">
                <el-icon class="text-gray-400">
                  <Picture />
                </el-icon>
              </div>
            </template>
          </el-image>
        </div>
      </div>

      <!-- 键盘快捷键提示 -->
      <div class="flex items-center justify-center mt-6 space-x-6 text-sm">
        <div class="flex items-center space-x-2">
          <kbd class="px-2 py-1 bg-black bg-opacity-30 rounded text-xs">←</kbd>
          <kbd class="px-2 py-1 bg-black bg-opacity-30 rounded text-xs">→</kbd>
          <span class="text-gray-500">切换图像</span>
        </div>
        <div class="flex items-center space-x-2">
          <kbd class="px-2 py-1 bg-black bg-opacity-30 rounded text-xs">ESC</kbd>
          <span class="text-gray-500">关闭预览</span>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-center">
        <el-button @click="closeImagePreview" size="default">
          关闭预览
        </el-button>
      </div>
    </template>
  </el-dialog>



  <!-- 通用操作确认弹窗（Element Plus 组件风格） -->
  <el-dialog v-model="actionConfirm.visible" width="520px" :close-on-click-modal="false" align-center
    :append-to-body="true">
    <template #header>
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
          <el-icon class="text-white">
            <Warning />
          </el-icon>
        </div>
        <div>
          <h3 class="text-base font-semibold text-gray-900">确认{{ actionConfirm.actionText }}</h3>
          <p class="text-xs text-gray-500 mt-0.5">请确认备份数据库与站点文件后继续</p>
        </div>
      </div>
    </template>
    <div class="space-y-3">
      <el-alert type="warning" :closable="false" show-icon title="重要提醒" class="rounded-md" />
      <div class="text-sm text-gray-700">
        将对应用 <span class="font-semibold">{{ actionConfirm.name }}</span> 执行 <span class="font-semibold">{{
          actionConfirm.actionText }}</span> 操作。
      </div>
      <ul class="list-disc pl-5 text-xs text-gray-600 space-y-1">
        <li>备份整站数据库</li>
        <li>备份网站代码文件</li>
        <li>确保服务器环境稳定</li>
      </ul>
      <div class="text-[12px] text-red-500">操作过程中请勿关闭页面或刷新浏览器</div>
    </div>
    <template #footer>
      <div class="flex justify-end space-x-3">
        <el-button @click="closeActionConfirm" :disabled="actionConfirm.loading">取消</el-button>
        <el-button type="primary" @click="confirmAction" :loading="actionConfirm.loading">
          <el-icon class="mr-1">
            <Upload />
          </el-icon>
          确认{{ actionConfirm.actionText }}
        </el-button>
      </div>
    </template>
  </el-dialog>

  <!-- 一键修复/安装 状态弹窗 -->
  <el-dialog v-model="fixStatus.visible" width="480px" :close-on-click-modal="false" :show-close="false" align-center>
    <template #header>
      <div class="flex items-center space-x-3">
        <el-icon class="text-blue-600 text-xl">
          <Refresh />
        </el-icon>
        <h3 class="text-base font-semibold text-gray-900">{{ fixStatus.actionText }}中</h3>
      </div>
    </template>
    <div class="py-4">
      <div class="flex items-center space-x-3">
        <el-icon class="animate-spin text-blue-500">
          <Loading />
        </el-icon>
        <div class="text-sm text-gray-700">{{ fixStatus.status }}</div>
      </div>
      <div class="mt-2 text-xs text-gray-500">目标应用：{{ actionConfirm.name }}</div>
    </div>
  </el-dialog>

  <!-- <cloud-build ref="cloudBuildRef" /> -->
</template>

<script lang="ts" setup>
import addonFile from "@/addon/tk_upgrade/views/base/addon-file/index.vue";
import { reactive, ref, toRefs, onMounted, onUnmounted, watch, nextTick } from "vue";
import { checkAuth, getAddonList, installAddon, getVersionList, updateAdmin } from "@/addon/tk_upgrade/api/upgrade";
import { Picture, Document, Close, Calendar, InfoFilled, Check, Upload, Download, Warning, Lock, Plus, Refresh, Box, Loading, Right, Delete, Tools, Monitor, ArrowLeft, ArrowRight } from '@element-plus/icons-vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { get } from "@vueuse/core";
import { deleteAddonDevelop } from '@/app/api/tools'
import {
  uninstallAddon,
  preUninstallCheck,
} from '@/addon/tk_upgrade/api/addon'
import { el } from "element-plus/es/locale";
// 框架更新相关状态
const frameworkUpdateDialog = ref(false)
const frameworkUpdateProgressDialog = ref(false)
const frameworkUpdateProgress = ref(0)
const frameworkUpdateStatus = ref('准备更新...')
const showAdminDialog = ref(false)
const adminUpdateProgressDialog = ref(false)
const adminUpdateStatus = ref('准备更新...')

// 通用操作确认弹窗（替代 ElMessageBox），使用 Element Plus 组件外观
const actionConfirm = reactive({
  visible: false,
  actionText: '',
  name: '',
  key: '',
  loading: false
})
const openActionConfirm = (payload: { key: string; name: string; actionText: string }) => {
  actionConfirm.key = payload.key
  actionConfirm.name = payload.name
  actionConfirm.actionText = payload.actionText
  actionConfirm.visible = true
}
const closeActionConfirm = () => {
  if (actionConfirm.loading) return
  actionConfirm.visible = false
}
// 一键修复状态弹窗状态
const fixStatus = reactive({
  visible: false,
  name: '',
  actionText: '',
  status: ''
})
const confirmAction = async () => {
  if (!actionConfirm.key) return
  actionConfirm.loading = true
  try {
    // 显示一键修复/安装状态弹窗
    fixStatus.visible = true
    fixStatus.name = actionConfirm.name
    fixStatus.actionText = actionConfirm.actionText
    fixStatus.status = '准备中...'
    let i = 0
    const steps = [
      '初始化环境...', '校验依赖...', '拉取安装包...', '安装与更新文件...', '刷新配置...', '清理缓存...', '验证安装结果...'
    ]
    const timer = setInterval(() => {
      fixStatus.status = steps[i % steps.length]
      i++
    }, 1200)

    // 卡片上对应应用的 loading 标记
    installLoading.value[actionConfirm.key] = true
    await installAddon(actionConfirm.key)

    clearInterval(timer)
    fixStatus.status = '完成'
    ElMessage.success(`${actionConfirm.actionText}成功`)

    // 刷新应用列表与当前版本信息
    const res: any = await getAddonList()
    addonList.value = res.data
    if (currentVersionInfo.value) {
      const updatedItem = res.data.find((it: any) => it.key === currentVersionInfo.value.key)
      if (updatedItem) currentVersionInfo.value = updatedItem
    }

    actionConfirm.visible = false
    fixStatus.visible = false
  } catch (e) {
    fixStatus.status = '失败'
    ElMessage.error(`${actionConfirm.actionText}失败`)
    fixStatus.visible = false
  } finally {
    actionConfirm.loading = false
    installLoading.value[actionConfirm.key] = false
  }
}

// 图像预览相关状态
const imagePreviewVisible = ref(false)
const previewImageUrl = ref('')
const previewImageList = ref<string[]>([])
const currentImageIndex = ref(0)


const updateAdminFn = async () => {
  showAdminDialog.value = false
  adminUpdateProgressDialog.value = true
  adminUpdateStatus.value = '🚀 正在启动前端更新...'

  try {
    // 更新状态提示循环
    const statusMessages = [
      '🚀 启动前端更新程序...',
      '🔍 检查服务器连接状态...',
      '📦 下载最新前端文件...',
      '🔧 安装新版本界面组件...',
      '🎨 更新样式和主题文件...',
      '⚙️ 优化前端性能配置...',
      '🧹 清理旧版本缓存...',
      '✅ 验证更新完整性...',
      '🎯 即将完成，请耐心等待...',
      '💡 更新过程中请勿关闭浏览器'
    ]

    let messageIndex = 0

    // 启动状态循环显示
    const statusInterval = setInterval(() => {
      adminUpdateStatus.value = statusMessages[messageIndex]
      messageIndex = (messageIndex + 1) % statusMessages.length
    }, 1500)

    // 执行真正的更新函数
    await updateAdmin()

    // 更新完成，停止状态循环
    clearInterval(statusInterval)

    // 显示完成状态
    adminUpdateStatus.value = '🎉 前端更新成功完成！'

    // 延迟后关闭弹窗并刷新页面
    setTimeout(() => {
      ElMessage.success('前端更新成功，页面即将刷新！')
      adminUpdateProgressDialog.value = false
      setTimeout(() => {
        window.location.reload()
      }, 1000)
    }, 2000)

  } catch (error) {
    console.error('前端更新失败:', error)
    ElMessage.error('前端更新失败，请稍后重试')
    adminUpdateStatus.value = '❌ 更新失败，请重试'

    // 3秒后关闭进度弹窗
    setTimeout(() => {
      adminUpdateProgressDialog.value = false
    }, 3000)
  }
}
// 显示框架更新确认弹窗
const showFrameworkUpdateDialog = () => {
  frameworkUpdateDialog.value = true
}

// 确认框架更新
const confirmFrameworkUpdate = async () => {
  frameworkUpdateDialog.value = false
  frameworkUpdateProgressDialog.value = true
  frameworkUpdateStatus.value = '🚀 正在启动框架升级...'

  try {
    // 升级状态提示循环（只显示文字，不显示进度）
    const statusMessages = [
      '🚀 启动框架升级程序...',
      '🔍 检测版本依赖...',
      '📦 下载最新版本框架文件...',
      '🔧 安装新版本核心文件...',
      '🗄️ 更新数据库结构和配置...',
      '🔄 重新编译系统组件...',
      '🧹 清理旧版本缓存文件...',
      '⚙️ 优化系统性能配置...',
      '✅ 验证升级完整性和稳定性...',
      '🎯 即将完成，请耐心等待...',
      '💡 升级过程中请勿关闭浏览器'
    ]

    let messageIndex = 0

    // 启动状态循环显示
    const statusInterval = setInterval(() => {
      frameworkUpdateStatus.value = statusMessages[messageIndex]
      messageIndex = (messageIndex + 1) % statusMessages.length
    }, 1500)

    // 立即执行真正的升级函数
    await installAddon('niucloud_saas')

    // 升级完成，停止状态循环
    clearInterval(statusInterval)

    // 显示完成状态
    frameworkUpdateStatus.value = '🎉 框架升级成功完成！'

    // 延迟后关闭弹窗并刷新页面
    setTimeout(() => {
      ElMessage.success('框架更新成功，页面即将刷新！')
      frameworkUpdateProgressDialog.value = false
      setTimeout(() => {
        window.location.reload()
      }, 1000)
    }, 2000)

  } catch (error) {
    console.error('框架更新失败:', error)
    ElMessage.error('框架更新失败，请稍后重试')
    frameworkUpdateStatus.value = '❌ 更新失败，请重试'
    frameworkUpdateProgressDialog.value = false
  }
}

// 处理框架更新（保留原有逻辑作为备用）
const handleFrameworkUpdate = () => {
  showFrameworkUpdateDialog()
}



const auth = ref()
const value = ref('addon')
checkAuth().then((res: any) => {
  auth.value = res.data
})

const addonList = ref()
getAddonList().then((res: any) => {
  addonList.value = res.data
})

const showversion = ref(false)
const currentVersionInfo = ref<any>(null)
const versionDialogVisible = ref(false)

const openVersonDialogFn = (item: any) => {
  getVersionListFn(item.key)
  currentVersionInfo.value = item
  versionDialogVisible.value = true
}

// 关闭版本记录弹窗
const handleVersionDialogClose = () => {
  versionDialogVisible.value = false
  currentVersionInfo.value = null
}
const frameDialog = ref(false)
const frameDialogFn = () => {
  getVersionListFn('niucloud_saas')
  frameDialog.value = true
}
// const cloudBuildRef = ref()
const versionList = ref()
const getVersionListFn = (key: any) => {
  getVersionList(key).then((res: any) => {
    versionList.value = res.data
  })
}

// 安装应用方法
const installAddonFn = (item: any) => {
  const actionText = item.is_install ? '更新' : '安装'

  openActionConfirm({ key: item.key, name: item.name, actionText })
  return

  ElMessageBox.confirm(
    `确定要${actionText}应用"${item.name}"吗？`,
    '重要提醒',
    {
      confirmButtonText: `确定${actionText}`,
      cancelButtonText: '取消',
      type: 'warning',
      dangerouslyUseHTMLString: true,
      message: `
        <div style="margin-bottom: 12px;">
          <strong style="color: #E6A23C;">⚠️ 重要提醒</strong>
        </div>
        <div style="margin-bottom: 8px;">
          在进行${actionText}操作前，强烈建议您：
        </div>
        <ul style="margin: 8px 0; padding-left: 20px; color: #666;">
          <li>备份整站数据库</li>
          <li>备份网站代码文件</li>
          <li>确保服务器环境稳定</li>
        </ul>
        <div style="color: #F56C6C; font-size: 12px;">
          ${actionText}过程中请勿关闭页面或刷新浏览器
        </div>
      `
    }
  ).then(() => {
    // 设置当前应用的加载状态
    installLoading.value[item.key] = true

    installAddon(item.key).then((res) => {
      // 更新成功后刷新列表
      getAddonList().then((res: any) => {
        addonList.value = res.data
      })
      ElMessage.success(`${actionText}成功`)
    }).catch((err) => {
      ElMessage.error(`${actionText}失败`)
    }).finally(() => {
      // 清除加载状态
      installLoading.value[item.key] = false
    })
  }).catch(() => {
    // 用户取消操作
  })
}

// 从弹窗中更新应用
const handleUpdateFromDialog = (item?: any) => {
  if (item && (item as any).key) currentVersionInfo.value = item
  if (!currentVersionInfo.value) {
    ElMessage.warning('未获取到当前应用信息')
    return
  }

  const actionText = currentVersionInfo.value.is_install ? '更新' : '安装'

  openActionConfirm({ key: currentVersionInfo.value.key, name: currentVersionInfo.value.name, actionText })
  return

  ElMessageBox.confirm(
    `确定要${actionText}应用"${currentVersionInfo.value.name}"吗？`,
    '重要提醒',
    {
      confirmButtonText: `确定${actionText}`,
      cancelButtonText: '取消',
      type: 'warning',
      dangerouslyUseHTMLString: true,
      message: `
        <div style="margin-bottom: 12px;">
          <strong style="color: #E6A23C;">⚠️ 重要提醒</strong>
        </div>
        <div style="margin-bottom: 8px;">
          在进行${actionText}操作前，强烈建议您：
        </div>
        <ul style="margin: 8px 0; padding-left: 20px; color: #666;">
          <li>备份整站数据库</li>
          <li>备份网站代码文件</li>
          <li>确保服务器环境稳定</li>
        </ul>
        <div style="color: #F56C6C; font-size: 12px;">
          ${actionText}过程中请勿关闭页面或刷新浏览器
        </div>
      `
    }
  ).then(() => {
    updateLoading.value = true
    // 同时设置卡片中的加载状态
    installLoading.value[currentVersionInfo.value.key] = true

    installAddon(currentVersionInfo.value.key).then((res) => {
      ElMessage.success(`${actionText}成功`)
      // 更新列表数据
      getAddonList().then((res: any) => {
        addonList.value = res.data
        // 更新当前弹窗中的应用信息
        const updatedItem = res.data.find((item: any) => item.key === currentVersionInfo.value.key)
        if (updatedItem) {
          currentVersionInfo.value = updatedItem
        }
      })
    }).catch((err) => {
      ElMessage.error(`${actionText}失败`)
    }).finally(() => {
      updateLoading.value = false
      installLoading.value[currentVersionInfo.value.key] = false
    })
  }).catch(() => {
    // 用户取消操作
  })
}

// 处理标签页点击
const handleClick = (tab: any) => {
  console.log('切换到标签页:', tab.props.name)
}

// 加载状态
const loading = ref(false)
const loadingText = ref('加载中...')
const updateLoading = ref(false)
const installLoading = ref<Record<string, boolean>>({})
const uninstallLoading = ref<Record<string, boolean>>({})
const batchLoading = ref({
  install: false,
  update: false
})

// 统计函数
const getInstalledCount = () => {
  return addonList.value?.filter((item: any) => item.is_install == 1 && item.is_update == 0).length || 0
}

const getUpdatableCount = () => {
  return addonList.value?.filter((item: any) => item.is_update == 1 && item.is_install == 1 && item.is_over == 0).length || 0
}

const getInstallableCount = () => {
  return addonList.value?.filter((item: any) => item.is_install == 0 && item.version != null).length || 0
}

// 一键安装全部
const handleInstallAll = () => {
  const installableApps = addonList.value?.filter((item: any) =>
    item.is_install == 0 && item.version != null
  ) || []

  if (installableApps.length === 0) {
    ElMessage.warning('没有可安装的应用')
    return
  }

  ElMessageBox.confirm(
    `确定要批量安装 ${installableApps.length} 个应用吗？`,
    '批量安装确认',
    {
      confirmButtonText: '确定安装',
      cancelButtonText: '取消',
      type: 'warning',
      dangerouslyUseHTMLString: true,
      message: `
        <div style="margin-bottom: 12px;">
          <strong style="color: #E6A23C;">⚠️ 重要提醒</strong>
        </div>
        <div style="margin-bottom: 8px;">
          即将批量安装以下应用：
        </div>
        <ul style="margin: 8px 0; padding-left: 20px; color: #666; max-height: 120px; overflow-y: auto;">
          ${installableApps.map((app: any) => `<li>${app.name} (v${app.version})</li>`).join('')}
        </ul>
        <div style="margin: 12px 0; padding: 8px; background: #fef3c7; border-radius: 4px; color: #92400e;">
          <strong>批量操作前，强烈建议您：</strong><br>
          • 备份整站数据库<br>
          • 备份网站代码文件<br>
          • 确保服务器环境稳定
        </div>
        <div style="color: #F56C6C; font-size: 12px;">
          批量安装过程中请勿关闭页面或刷新浏览器
        </div>
      `
    }
  ).then(async () => {
    batchLoading.value.install = true
    let successCount = 0
    let failCount = 0

    // 显示开始提示
    ElMessage.info(`开始批量安装 ${installableApps.length} 个应用...`)

    for (let i = 0; i < installableApps.length; i++) {
      const app = installableApps[i]
      try {
        // 设置当前应用的加载状态
        installLoading.value[app.key] = true

        // 显示当前进度
        loadingText.value = `正在安装 ${app.name} (${i + 1}/${installableApps.length})`

        await installAddon(app.key)
        successCount++

        ElMessage.success(`${app.name} 安装成功`)
      } catch (error) {
        failCount++
        ElMessage.error(`${app.name} 安装失败`)
        console.error(`安装 ${app.name} 失败:`, error)
      } finally {
        // 清除当前应用的加载状态
        installLoading.value[app.key] = false
      }
    }

    // 刷新应用列表
    getAddonList().then((res: any) => {
      addonList.value = res.data
    })

    // 显示结果
    if (failCount === 0) {
      ElMessage.success(`批量安装完成！成功安装 ${successCount} 个应用`)
    } else {
      ElMessage.warning(`批量安装完成！成功 ${successCount} 个，失败 ${failCount} 个`)
    }

    loadingText.value = '加载中...'
    batchLoading.value.install = false
  }).catch(() => {
    // 用户取消操作
  })
}

// 一键更新全部
const handleUpdateAll = () => {
  const updatableApps = addonList.value?.filter((item: any) =>
    item.is_update == 1 && item.is_install == 1 && item.is_over == 0
  ) || []

  if (updatableApps.length === 0) {
    ElMessage.warning('没有可更新的应用')
    return
  }

  ElMessageBox.confirm(
    `确定要批量更新 ${updatableApps.length} 个应用吗？`,
    '批量更新确认',
    {
      confirmButtonText: '确定更新',
      cancelButtonText: '取消',
      type: 'warning',
      dangerouslyUseHTMLString: true,
      message: `
        <div style="margin-bottom: 12px;">
          <strong style="color: #E6A23C;">⚠️ 重要提醒</strong>
        </div>
        <div style="margin-bottom: 8px;">
          即将批量更新以下应用：
        </div>
        <ul style="margin: 8px 0; padding-left: 20px; color: #666; max-height: 120px; overflow-y: auto;">
          ${updatableApps.map((app: any) => `<li>${app.name} (v${app.local_version} → v${app.version})</li>`).join('')}
        </ul>
        <div style="margin: 12px 0; padding: 8px; background: #fef3c7; border-radius: 4px; color: #92400e;">
          <strong>批量更新前，强烈建议您：</strong><br>
          • 备份整站数据库<br>
          • 备份网站代码文件<br>
          • 确保服务器环境稳定<br>
          • 在低峰期进行操作
        </div>
        <div style="color: #F56C6C; font-size: 12px;">
          批量更新过程中请勿关闭页面或刷新浏览器
        </div>
      `
    }
  ).then(async () => {
    batchLoading.value.update = true
    let successCount = 0
    let failCount = 0

    // 显示开始提示
    ElMessage.info(`开始批量更新 ${updatableApps.length} 个应用...`)

    for (let i = 0; i < updatableApps.length; i++) {
      const app = updatableApps[i]
      try {
        // 设置当前应用的加载状态
        installLoading.value[app.key] = true

        // 显示当前进度
        loadingText.value = `正在更新 ${app.name} (${i + 1}/${updatableApps.length})`

        await installAddon(app.key)
        successCount++

        ElMessage.success(`${app.name} 更新成功`)
      } catch (error) {
        failCount++
        ElMessage.error(`${app.name} 更新失败`)
        console.error(`更新 ${app.name} 失败:`, error)
      } finally {
        // 清除当前应用的加载状态
        installLoading.value[app.key] = false
      }
    }

    // 刷新应用列表
    getAddonList().then((res: any) => {
      addonList.value = res.data
    })

    // 显示结果
    if (failCount === 0) {
      ElMessage.success(`批量更新完成！成功更新 ${successCount} 个应用`)
    } else {
      ElMessage.warning(`批量更新完成！成功 ${successCount} 个，失败 ${failCount} 个`)
    }

    loadingText.value = '加载中...'
    batchLoading.value.update = false
  }).catch(() => {
    // 用户取消操作
  })
}

// 图像预览相关方法
const openImagePreview = (imageUrl: string, allImages?: string[]) => {
  previewImageUrl.value = imageUrl
  previewImageList.value = allImages || [imageUrl]
  currentImageIndex.value = previewImageList.value.indexOf(imageUrl)
  imagePreviewVisible.value = true
}

const closeImagePreview = () => {
  imagePreviewVisible.value = false
  previewImageUrl.value = ''
  previewImageList.value = []
  currentImageIndex.value = 0
}

const nextImage = () => {
  if (currentImageIndex.value < previewImageList.value.length - 1) {
    currentImageIndex.value++
    previewImageUrl.value = previewImageList.value[currentImageIndex.value]
  }
}

const previousImage = () => {
  if (currentImageIndex.value > 0) {
    currentImageIndex.value--
    previewImageUrl.value = previewImageList.value[currentImageIndex.value]
  }
}

// 键盘事件处理
const handleKeydown = (event: KeyboardEvent) => {
  if (!imagePreviewVisible.value) return

  switch (event.key) {
    case 'ArrowLeft':
      event.preventDefault()
      previousImage()
      break
    case 'ArrowRight':
      event.preventDefault()
      nextImage()
      break
    case 'Escape':
      event.preventDefault()
      closeImagePreview()
      break
  }
}

// 格式化版本内容并添加图像预览功能
const formatVersionContent = (content: string) => {
  if (!content) return ''

  // 将换行符转换为 <br> 标签
  let formatted = content.replace(/\n/g, '<br>')

  // 处理列表项（以 - 或 * 开头的行）
  formatted = formatted.replace(/^[-*]\s+(.+)$/gm, '<li class="ml-4 mb-1">• $1</li>')

  // 处理数字列表项
  formatted = formatted.replace(/^\d+\.\s+(.+)$/gm, '<li class="ml-4 mb-1 list-decimal">$1</li>')

  // 处理图像标签，添加点击预览功能
  formatted = formatted.replace(
    /<img([^>]*?)src=["']([^"']+)["']([^>]*?)>/gi,
    (match, before, src, after) => {
      const imageId = `img_${Math.random().toString(36).substr(2, 9)}`
      return `<img${before}src="${src}"${after} class="cursor-pointer hover:opacity-80 transition-opacity duration-200 max-w-full h-auto rounded-lg shadow-sm" onclick="window.previewImage && window.previewImage('${src}', '${imageId}')" id="${imageId}" style="max-height: 200px; object-fit: contain;">`
    }
  )

  return formatted
}
const handleUninstallAddon = (key: string) => {

}

// 全局图像预览函数设置
const setupGlobalImagePreview = () => {
  // 将预览函数挂载到 window 对象上，供 HTML 中的 onclick 调用
  ; (window as any).previewImage = (imageUrl: string, imageId: string) => {
    // 收集当前版本内容中的所有图像
    const versionContentEl = document.querySelector('.version-content')
    if (versionContentEl) {
      const images = Array.from(versionContentEl.querySelectorAll('img'))
      const imageUrls = images.map(img => (img as HTMLImageElement).src)
      openImagePreview(imageUrl, imageUrls)
    } else {
      openImagePreview(imageUrl)
    }
  }
}

// 生命周期钩子
onMounted(() => {
  setupGlobalImagePreview()
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  // 清理全局函数
  if ((window as any).previewImage) {
    delete (window as any).previewImage
  }
})

// 卸载应用方法
const uninstallAddonFn = (item: any) => {
  if (item.key == 'tk_upgrade') {
    ElMessage.warning('此插件为系统核心插件，无法卸载！')
    return
  }
  preUninstallCheck(item.key).then(({ data }) => {
    if (data.is_pass) {
      uninstallAddon({ addon: item.key }).then((res) => {
        deleteAddonDevelop(item.key).then(() => {
          // 刷新应用列表
          getAddonList().then((res: any) => {
            addonList.value = res.data
          })
        })
        loading.value = false
      }).catch(() => {
        loading.value = false
      })
    }
  })
}
</script>

<style lang="scss" scoped>
.addon-card {
  transition: all 0.3s ease;
  border: 1px solid #e4e7ed;
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  height: 100%;

  &:hover {
    transform: translateY(-2px);
    border-color: #409eff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  &.installed {
    border-color: #67c23a;

    :deep(.el-card__header) {
      background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    }
  }

  &.has-update {
    border-color: #e6a23c;

    :deep(.el-card__header) {
      background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%);
    }
  }

  :deep(.el-card__body) {
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  :deep(.el-card__footer) {
    margin-top: auto;
  }
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* 弹窗样式优化 */
.version-dialog {
  .el-dialog__header {
    padding: 0;
  }

  .el-dialog__body {
    padding: 0;
  }

  .el-dialog__footer {
    padding: 0;
  }
}

/* 自定义滚动条 */
.custom-scrollbar {
  &::-webkit-scrollbar {
    width: 6px;
  }

  &::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
  }

  &::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;

    &:hover {
      background: #a8a8a8;
    }
  }
}

/* 版本内容样式 */
.version-content {
  li {
    color: #666;
    margin-bottom: 4px;
  }

  ul {
    margin: 8px 0;
  }
}

/* 现代化标签页样式 */
.modern-tabs {
  :deep(.el-tabs__header) {
    margin: 0 0 20px 0;
    border-bottom: 2px solid #f0f0f0;
  }

  :deep(.el-tabs__nav-wrap::after) {
    display: none;
  }

  :deep(.el-tabs__item) {
    font-weight: 500;
    color: #666;

    &.is-active {
      color: #409eff;
      font-weight: 600;
    }
  }

  :deep(.el-tabs__active-bar) {
    height: 3px;
    border-radius: 2px;
  }
}

/* 动画效果 */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* 按钮加载状态动画 */
.el-button.is-loading {
  position: relative;

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.1);
    border-radius: inherit;
    animation: pulse 1.5s ease-in-out infinite;
  }
}

@keyframes pulse {

  0%,
  100% {
    opacity: 0.3;
  }

  50% {
    opacity: 0.7;
  }
}

/* 卡片加载状态 */
.addon-card.loading {
  position: relative;

  &::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(2px);
    border-radius: inherit;
    z-index: 10;
  }
}

/* 进度指示器 */
.progress-indicator {
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #409eff, transparent);
    animation: progress 2s ease-in-out infinite;
  }
}

@keyframes progress {
  0% {
    left: -100%;
  }

  100% {
    left: 100%;
  }
}

/* 卡片悬停效果 */
.card-hover {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  }
}

/* 统计卡片动画 */
.stat-card {
  transition: all 0.2s ease;

  &:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }
}

/* 更新按钮动画 */
.update-button {
  position: relative;
  overflow: hidden;

  &.loading {
    &::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      animation: shimmer 2s infinite;
    }
  }
}

@keyframes shimmer {
  0% {
    left: -100%;
  }

  100% {
    left: 100%;
  }
}

/* 版本标签动画 */
.version-tag {
  transition: all 0.2s ease;

  &:hover {
    transform: scale(1.05);
  }
}

/* 脉冲动画 */
@keyframes pulse-glow {

  0%,
  100% {
    box-shadow: 0 0 5px rgba(64, 158, 255, 0.3);
  }

  50% {
    box-shadow: 0 0 20px rgba(64, 158, 255, 0.6);
  }
}

.pulse-glow {
  animation: pulse-glow 2s ease-in-out infinite;
}

/* 加载状态的渐变背景 */
.loading-gradient {
  background: linear-gradient(45deg, #409eff, #67c23a, #409eff);
  background-size: 200% 200%;
  animation: gradient-shift 3s ease infinite;
}

@keyframes gradient-shift {
  0% {
    background-position: 0% 50%;
  }

  50% {
    background-position: 100% 50%;
  }

  100% {
    background-position: 0% 50%;
  }
}

/* 成功状态动画 */
@keyframes success-bounce {

  0%,
  20%,
  50%,
  80%,
  100% {
    transform: translateY(0);
  }

  40% {
    transform: translateY(-3px);
  }

  60% {
    transform: translateY(-1px);
  }
}

.success-bounce {
  animation: success-bounce 0.6s ease;
}

/* 框架更新弹窗样式 */
.framework-update-dialog {
  :deep(.el-dialog) {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    backdrop-filter: blur(10px);
  }

  :deep(.el-dialog__header) {
    padding: 0;
    margin: 0;
    border-bottom: 1px solid #f1f5f9;
  }

  :deep(.el-dialog__body) {
    padding: 0;
  }

  :deep(.el-dialog__footer) {
    padding: 0;
    margin: 0;
    border-top: 1px solid #f1f5f9;
  }
}



/* 框架更新进度弹窗样式 */
.framework-progress-dialog {
  :deep(.el-dialog) {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(15px);
    background: rgba(255, 255, 255, 0.95);
  }

  :deep(.el-dialog__header) {
    padding: 0;
    margin: 0;
  }

  :deep(.el-dialog__body) {
    padding: 0;
  }

  :deep(.el-progress-bar__outer) {
    border-radius: 10px;
    overflow: hidden;
    background: rgba(59, 130, 246, 0.1);
  }

  :deep(.el-progress-bar__inner) {
    border-radius: 10px;
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    background: linear-gradient(90deg, #3b82f6, #6366f1);
  }
}

/* 脉冲发光动画增强 */
@keyframes pulse-glow {

  0%,
  100% {
    box-shadow: 0 0 5px rgba(59, 130, 246, 0.5);
  }

  50% {
    box-shadow: 0 0 20px rgba(59, 130, 246, 0.8), 0 0 30px rgba(59, 130, 246, 0.4);
  }
}

/* 旋转动画优化 */
@keyframes spin-smooth {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin-smooth 2s linear infinite;
}

/* 进度环动画 */
@keyframes progress-ring {
  0% {
    stroke-dasharray: 0 283;
  }

  100% {
    stroke-dasharray: 283 283;
  }
}

/* 图像预览弹窗样式 */
.image-preview-dialog {
  :deep(.el-dialog) {
    background: rgba(0, 0, 0, 0.95);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }

  :deep(.el-dialog__header) {
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 16px 24px;
  }

  :deep(.el-dialog__body) {
    background: rgba(0, 0, 0, 0.95);
    padding: 20px;
  }

  :deep(.el-dialog__footer) {
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(10px);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding: 16px 24px;
  }
}

.image-preview-container {
  .el-image {
    transition: all 0.3s ease;
  }

  kbd {
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Code', 'Fira Mono', 'Droid Sans Mono', 'Consolas', monospace;
    font-size: 11px;
    font-weight: 600;
    line-height: 1;
    min-width: 20px;
    text-align: center;
  }
}

/* 版本内容中的图像样式 */
.version-content {
  img {
    transition: all 0.2s ease;
    border: 1px solid #e5e7eb;

    &:hover {
      transform: scale(1.02);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      border-color: #3b82f6;
    }
  }
}
</style>