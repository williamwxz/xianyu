<template>
  <div class="main-container p-4">
    <el-card v-loading="loading" :element-loading-text="loadingText" class="box-card !border-none mt-2" shadow="never">
      <el-tabs v-model="value" @tab-click="handleClick">
        <el-tab-pane label="安装包" name="admin">
          <div style="width: 640px" class="mb-2 mt-6">
            <el-alert type="error" title="提示:上传安装包会自动解析应用信息,未安装应用将会自动安装，已经安装会自动替换成最新上传包，请手动备份数据库和全站代码"
              :closable="false" />
          </div>
          <div class="flex mt-6 mb-6">
            <div class="w-[320px]">
              <addon-file v-model="file_url" api="sys/document/applet">
                <div class="d-card p-4">
                  <el-icon size="38" color="#409efc">
                    <FolderOpened />
                  </el-icon>
                  <div class="text-[#409efc]">上传安装包</div>
                </div>
              </addon-file>
            </div>
            <div class="w-[320px]" @click="handleCloudBuild()">
              <div class="d-card p-4">
                <el-icon size="38" color="#409efc">
                  <MostlyCloudy />
                </el-icon>
                <div class="text-[#409efc]">一键云编译</div>
              </div>
            </div>
          </div>
        </el-tab-pane>
      </el-tabs>
    </el-card>
  </div>
  <cloud-build ref="cloudBuildRef" />
</template>
<script lang="ts" setup>
import addonFile from "@/addon/tk_upgrade/views/base/addon-file/index.vue";
import { reactive, ref, toRefs, onMounted, onUnmounted, watch } from "vue";
import { addonUpload, checkAuthInfo } from "@/addon/tk_upgrade/api/base";
import { img } from "@/utils/common";
import { t } from "@/lang";
import { ElMessageBox } from "element-plus";
import { useRouter, useRoute } from "vue-router";
import type { FormInstance } from "element-plus";
import CloudBuild from "@/addon/tk_upgrade/views/base/cloud-build/index.vue";
const upgradeRef = ref<any>(null);
const cloudBuildRef = ref<any>(null);
const getAuthCodeDialog: Record<string, any> | null = ref(null);

const authCodeApproveDialog = ref(false);
const handleCloudBuild = () => {
  checkAuthInfo();
  if (cloudBuildRef.value.cloudBuildTask) {
    cloudBuildRef.value?.open();
    return;
  }
  ElMessageBox.confirm(
    "如多个应用插件升级，可上传完成后再执行云编译，是否确认执行云编译？",
    "云编译",
    {
      confirmButtonText: "确认",
      cancelButtonText: "取消",
      type: "warning",
    }
  ).then(() => {
    cloudBuildRef.value?.open();
  });
};

const route = useRoute();
const router = useRouter();
const pageName = route.meta.title;
const searchFormRef = ref(null);

const value = ref("admin");
const loading = ref(false);
const loadingText = ref("准备中...");
const file_url = ref();

const addonUploadEvent = () => {
  if (file_url.value) {
    loading.value = true;
    loadingText.value = "正在上传安装包...";
    addonUpload({ file_url: file_url.value }).then((res) => {
      loadingText.value = "上传完成，准备解压安装包...";
      handleCloudBuild();
      loading.value = false;
      file_url.value = "";
    }).catch(() => {
      loading.value = false;
      loadingText.value = "准备中...";
    });
  }
};

// 使用watch监听file_url的变化
watch(file_url, (newVal) => {
  if (newVal) {
    // 当file_url有值时，执行addonUploadEvent
    addonUploadEvent();
  }
});
</script>


<style lang="scss" scoped>
.d-card {
  margin-top: 12px;
  width: 180px;
  text-align: center;
  background: linear-gradient(127deg,
      rgba(156, 156, 229, 0.1),
      #b8ffd833 70.71%);
  color: aliceblue;
  backdrop-filter: blur(10px);
  font-size: 18px;
  border-radius: 18px;
}
</style>
