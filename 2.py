#!/usr/bin/env python3
# encoding=utf-8

import jieba

jieba.load_userdict("userdict.txt")

seg_list = jieba.cut("他来到了网易杭研大厦")  # 默认是精确模式
print(", ".join(seg_list))