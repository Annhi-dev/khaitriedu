import pathlib, re, os
mapping={
 'User':'nguoi_dung',
 'Session':'phien',
 'OtpCode':'ma_otp',
 'Category':'danh_muc',
 'Subject':'mon_hoc',
 'Course':'khoa_hoc',
 'Module':'chuong_hoc',
 'Lesson':'bai_hoc',
 'Quiz':'bai_trac_nghiem',
 'Question':'cau_hoi',
 'Option':'lua_chon',
 'QuizAnswer':'cau_tra_loi',
 'Enrollment':'dang_ky',
 'Grade':'diem',
 'Review':'danh_gia',
 'TeacherApplication':'ung_tuyen_giang_vien',
 'Certificate':'chung_nhan',
 'Comment':'binh_luan',
 'Notification':'thong_bao',
 'Announcement':'thong_bao_chung',
 'Attachment':'tap_tin_dinh_kem',
 'LessonProgress':'tien_do_bai_hoc',
}
os.chdir('d:/XXamp/htdocs/khaitriedu/app/Models')
for model, table in mapping.items():
    path=pathlib.Path(f'{model}.php')
    if not path.exists():
        print('missing', path)
        continue
    text=path.read_text(encoding='utf-8')
    if 'protected $table' in text:
        text=re.sub(r"protected \$table\s*=\s*'[^']*';", f"protected $table = '{table}';", text)
    else:
        idx=text.find('class '+model)
        if idx!=-1:
            insert_idx=text.find('{',idx)
            if insert_idx!=-1:
                insert_idx=text.find('\n', insert_idx)+1
                text=text[:insert_idx]+f"    protected $table = '{table}';\n\n"+text[insert_idx:]
    path.write_text(text,encoding='utf-8')
    print('updated', path)
