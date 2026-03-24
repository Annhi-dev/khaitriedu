import os, re
folder='database/migrations'
metric={'create':'tao_bang','add':'them_cot','alter':'sua_doi','modify':'chinh_sua','update':'cap_nhat'}
map_table={'users':'nguoi_dung','sessions':'phien_dang_nhap','otp_codes':'ma_otp','subjects':'mon_hoc','courses':'khoa_hoc','modules':'chuong_hoc','enrollments':'dang_ky_hoc','schedule':'lich_hoc','reviews':'danh_gia','grades':'diem_so','teacher_applications':'don_xin_lam_giao_vien','lessons':'bai_hoc','quizzes':'bai_kiem_tra','questions':'cau_hoi','options':'lua_chon','quiz_answers':'cau_tra_loi','certificates':'chung_chi','comments':'binh_luan','notifications':'thong_bao','announcements':'thong_bao_chung','attachments':'tai_lieu_dinh_kem','lesson_progress':'tien_do_bai_hoc','categories':'danh_muc','role':'vai_tro','price':'gia','image':'anh','username':'ten_dang_nhap','phone':'so_dien_thoai'}
# display rename mapping and apply file rename if exists
for fname in sorted(os.listdir(folder)):
    if not fname.endswith('.php'): continue
    parts=fname.split('_')
    if len(parts) < 5: continue
    timestamp='_'.join(parts[:4])
    action_part='_'.join(parts[4:])
    base_name=action_part[:-4]
    name_words=base_name.split('_')
    converted=[metric.get(w.lower(), map_table.get(w.lower(), w)) for w in name_words]
    newfname=timestamp + '_' + '_'.join(converted) + '.php'
    if newfname != fname:
        oldpath=os.path.join(folder,fname)
        newpath=os.path.join(folder,newfname)
        if not os.path.exists(newpath):
            os.rename(oldpath,newpath)
            print('rename', fname, '->', newfname)
        else:
            print('skip exists', newfname)
# update content inside files
for fname in sorted(os.listdir(folder)):
    if not fname.endswith('.php'): continue
    path=os.path.join(folder,fname)
    with open(path,'r',encoding='utf-8') as f:
        text=f.read()
    for old,new in map_table.items():
        text = re.sub(r"('|")%s('|")" % re.escape(old), r"\1%s\2" % new, text)
    with open(path,'w',encoding='utf-8') as f:
        f.write(text)
print('done')
